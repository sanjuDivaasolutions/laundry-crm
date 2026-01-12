<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\Admin\PaymentResource;
use App\Http\Resources\Admin\PaymentEditResource;
use App\Models\Payment;
use App\Models\SalesInvoice;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class PaymentApiController extends Controller
{
    protected $className = Payment::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PaymentResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        $type = request('type');
        $id = request('id');

        $query = Payment::query()
            ->with([
                'salesInvoice:id,invoice_number',
                'purchaseInvoice:id,invoice_number',
                'salesOrder:id,so_number',
                'purchaseOrder:id,po_number',
                'user:id,name',
                'paymentMode:id,name',
            ]);
        if ($type) {
            $query->where('payment_type', $type);
        }

        if ($type == 'si') {
            $query->where('sales_invoice_id', $id);
        } else if ($type == 'pi') {
            $query->where('purchase_invoice_id', $id);
        } else if ($type == 'so') {
            $query->where('sales_order_id', $id);
        } else if ($type == 'po') {
            $query->where('purchase_order_id', $id);
        }

        return PaymentResource::collection($query->advancedFilter());
    }

    public function store(StorePaymentRequest $request)
    {
        if ($request->payment_type === 'si' && $request->sales_invoice_id) {
            $invoice = SalesInvoice::find($request->sales_invoice_id);
            if ($invoice && $invoice->syncPaymentStatus() === 'paid') {
                $message = __('Payment cannot be recorded because this sales invoice is already marked as paid.');
                return response()->json([
                    'message' => $message,
                    'errors'  => [
                        'sales_invoice_id' => [$message],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $payment = Payment::create($request->validated());
        
        $this->updateSalesInvoicePaymentStatus($payment);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaults = [];

        // Get sales invoice ID from query parameters
        $salesInvoiceId = request('sales_invoice_id');
        $purchaseInvoiceId = request('purchase_invoice_id');


        if ($salesInvoiceId) {
            $salesInvoice = \App\Models\SalesInvoice::with('payments')->find($salesInvoiceId);

            if ($salesInvoice) {
                // Pre-populate with current date and pending amount for sales invoice
                $defaults = [
                    'payment_date'     => now()->format(config('project.date_format')),
                    'amount'           => $salesInvoice->pending_amount,
                    'tran_type'        => 'receive',
                    'payment_type'     => 'si',
                    'sales_invoice_id' => $salesInvoice->id,
                    'sales_order_id'   => $salesInvoice->sales_order_id,
                ];
            }
        } elseif ($purchaseInvoiceId) {
            $purchaseInvoice = \App\Models\Inward::find($purchaseInvoiceId);

            if ($purchaseInvoice) {
                // Pre-populate with current date and pending amount for purchase invoice
                $pendingAmount = $purchaseInvoice->grand_total - ($purchaseInvoice->total_paid ?? 0);
                $defaults = [
                    'payment_date'        => now()->format(config('project.date_format')),
                    'amount'              => max(0, $pendingAmount),
                    'tran_type'           => 'send',
                    'payment_type'        => 'pi',
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'purchase_order_id'   => $purchaseInvoice->purchase_order_id,
                ];
            }
        }

        return response([
            'defaults' => $defaults,
            'meta'     => [],
        ]);
    }

    public function show(Payment $payment)
    {
        abort_if(Gate::denies('payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PaymentResource($payment);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());
        
        $this->updateSalesInvoicePaymentStatus($payment);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Payment $payment)
    {
        abort_if(Gate::denies('payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $payment->load(['paymentMode:id,name']);

        return response([
            'data' => new PaymentEditResource($payment),
            'meta' => [],
        ]);
    }

    public function destroy(Payment $payment)
    {
        abort_if(Gate::denies('payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salesInvoiceId = $payment->sales_invoice_id;
        $payment->delete();
        
        if ($salesInvoiceId) {
            $salesInvoice = SalesInvoice::find($salesInvoiceId);
            if ($salesInvoice) {
                $this->updateSalesInvoicePaymentStatus($salesInvoice);
            }
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function getPayments($type, $id)
    {
        $query = Payment::query()
            ->with([
                'salesInvoice',
                'purchaseInvoice',
                'salesOrder',
                'purchaseOrder',
                'user',
            ]);
        if ($type == 'si') {
            $query->where(function ($q) use ($id) {
                $q->whereHas('salesInvoice', function ($q) use ($id) {
                    $q->where('id', $id);
                });
            });
        } else if ($type == 'pi') {
            $query->where(function ($q) use ($id) {
                $q->whereHas('purchaseInvoice', function ($q) use ($id) {
                    $q->where('id', $id);
                });
            });
        }

        return PaymentResource::collection($query->advancedFilter());
    }

    private function updateSalesInvoicePaymentStatus($paymentOrInvoice)
    {
        $salesInvoice = null;
        
        if ($paymentOrInvoice instanceof Payment && $paymentOrInvoice->sales_invoice_id) {
            $salesInvoice = SalesInvoice::find($paymentOrInvoice->sales_invoice_id);
        } elseif ($paymentOrInvoice instanceof SalesInvoice) {
            $salesInvoice = $paymentOrInvoice;
        }
        
        if (!$salesInvoice) {
            return;
        }

        $salesInvoice->syncPaymentStatus();
    }
}
