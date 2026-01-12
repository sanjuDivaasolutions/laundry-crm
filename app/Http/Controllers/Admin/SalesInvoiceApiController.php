<?php
/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 12/02/25, 5:02 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesInvoiceRequest;
use App\Http\Requests\UpdateSalesInvoiceRequest;
use App\Http\Resources\Admin\SalesInvoiceListResourceCollection;
use App\Http\Resources\Admin\SalesInvoiceResource;
use App\Models\Buyer;
use App\Models\PaymentTerm;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\CanadaTaxService;
use App\Services\CompanyService;
use App\Services\ControllerService;
use App\Services\DatabaseService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class SalesInvoiceApiController extends Controller
{
    protected $className = SalesInvoice::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = SalesInvoiceResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['invoice_number', 'reference_no'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
        ['request' => 'f_payment_status', 'field' => 'payment_status', 'operator' => 'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('sales_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //$this->updateInvoicePaymentStatuses();

        return $this->getList();
    }

    public function getList()
    {
        $relations = array_filter([
            $this->buyerRelation(['agent_name', 'commission_rate']),
            'company:id,name',
            'user:id,name',
        ]);

        return new SalesInvoiceListResourceCollection(
            SalesInvoice::query()
                ->where('order_type', 'product')
                ->with($relations)
                ->reorder()
                ->latest()
                ->advancedFilter()
                
        );
    }

    /**
     * @throws \Exception
     */
    public function store(StoreSalesInvoiceRequest $request)
    {
        $salesInvoice = null;
        DatabaseService::executeTransaction(function () use ($request, &$salesInvoice) {
            $salesInvoice = SalesInvoice::query()->create($request->validated());
            $this->updateRelatives($request, $salesInvoice);
        });
        return (new SalesInvoiceResource($salesInvoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('sales_invoice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaultPaymentTermId = config('system.defaults.payment_term_id', 3);
        $paymentTerm = PaymentTerm::query()
            ->select('id', 'name')
            ->find($defaultPaymentTermId);

        $company = CompanyService::getDefaultCompanyEntry();

        $defaults = [
            'date'         => Carbon::now()->format(config('project.date_format')),
            'company'      => $company,
            'warehouse'    => $company->warehouse,
            'payment_term' => $paymentTerm,
            'state'        => CanadaTaxService::getDefaultStateObject(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('sales_invoice_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        InvoiceService::setupTaxes($salesInvoice);

        $salesInvoice->load(array_filter([
                $this->buyerRelation([
                    'agent_name',
                    'commission_rate',
                    'billing_address_id',
                    'shipping_address_id',
                    'name',
                ]),
                'buyer.agent:id,name,display_name',
                'agent:id,name,display_name',
            'buyer.billingAddress.city:id,name',
            'buyer.billingAddress.state:id,name',
            'paymentTerm:id,name',
            'items.product:id,name',
            'items.unit:id,name',
            'taxes:id,amount,tax_rate_id,taxable_type,taxable_id',
            'taxes.taxRate:id,name,rate',
        ]));
        return new SalesInvoiceResource($salesInvoice);
    }

    public function update(UpdateSalesInvoiceRequest $request, SalesInvoice $salesInvoice)
    {
        DatabaseService::executeTransaction(function () use ($request, $salesInvoice) {
            $salesInvoice->update($request->validated());
            $this->updateRelatives($request, $salesInvoice);
        });
        return (new SalesInvoiceResource($salesInvoice))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('sales_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new SalesInvoiceResource($salesInvoice->load(array_filter([
                'company:id,name',
                'state:id,name',
                $this->buyerRelation(['agent_name', 'commission_rate', 'name']),
                'buyer.agent:id,name,display_name',
                'agent:id,name,display_name',
                'items:id,sales_invoice_id,unit_id,shelf_id,product_id,rate,quantity,amount',
                'items.product:id,name',
                'items.unit:id,name',
                'items.shelf:id,name',
                'warehouse:id,name',
                'paymentTerm:id,name',
                'salesOrder:id,so_number',
                'taxes',
            ]))),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('sales_invoice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($salesInvoice) {

            $items = $salesInvoice->items()->get();
            // save product id in a variable and then delete the item and then update stock
            foreach ($items as $item) {
                $product_id = $item->product_id;
                $item->inventory()->delete();
                $item->delete();
                InventoryService::updateProductStockInWarehouse($product_id, $salesInvoice->warehouse_id);
            }

            $salesInvoice->delete();
        });
        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTaxes($request, $obj);
        $this->updateTotals($obj);
        $this->updateStocks($obj);
    }

    public function updateStocks($obj)
    {
        InventoryService::updateStockBasedOnOrder($obj, 'sales');
    }

    private function updateTaxes($request, $obj)
    {
        InvoiceService::updateTaxes($request, $obj, SalesInvoice::class);
    }

    private function updateTotals($obj)
    {
        OrderService::updateSalesOrderTotals($obj);
    }

    private function updateItems($request, $obj)
    {
        $items = InvoiceService::getItemsObject($request);
        $request->merge(['items' => $items]);
        ControllerService::updateChild($request, $obj, 'items', SalesInvoiceItem::class, 'items', 'sales_invoice_id');
    }

    public function getSinglePdf(SalesInvoice $salesInvoice)
    {
        InvoiceService::setupTaxes($salesInvoice);

        $salesInvoice->load([
            'company',
            $this->buyerRelation(['name', 'billing_address_id', 'shipping_address_id']),
            'buyer.billingAddress:id,name,address_1,address_2,city_id,state_id,postal_code,phone',
            'buyer.billingAddress.city:id,name',
            'paymentTerm:id,name',
            'items' => function ($q) {
                $q->groupBy('product_id', 'rate');
                $q->selectRaw('*, SUM(quantity) as total_quantity, SUM(amount) as total_amount');
            },
            'items.product:id,name',
            'items.unit:id,name',
        ]);

        $outstanding = InvoiceService::getOutstandingInvoicesForBuyer($salesInvoice);
        $currentPending = $salesInvoice->pending_amount;
        $totalPaymentDue = $currentPending + ($outstanding['total'] ?? 0);

        $code = $salesInvoice->invoice_number;
        $fileNamePrefix = $salesInvoice->order_type == 'product' ? 'sales' : 'service';
        $fileName = "$fileNamePrefix-invoice-$code.pdf";
        $compact = [
            'obj'               => $salesInvoice,
            'outstanding'       => $outstanding,
            'currentPending'    => $currentPending,
            'totalPaymentDue'   => $totalPaymentDue,
        ];
        $pdf = Pdf::loadView('pdf-templates.sales-invoice.sales-invoice', $compact);
        return $pdf->download($fileName);
    }

    public function updatePaymentStatus(Request $request, SalesInvoice $salesInvoice)
    {
        abort_if(Gate::denies('sales_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $table = $salesInvoice->getTable();
        if (!Schema::hasColumn($table, 'payment_status')) {
            return response([
                'message' => 'Payment status tracking is not available on this system. Please run the latest migrations to add the payment_status column.',
            ], Response::HTTP_NOT_IMPLEMENTED);
        }

        DatabaseService::executeTransaction(function () use ($salesInvoice) {
            $salesInvoice->syncPaymentStatus();
        });

        $salesInvoice->refresh();

        return response([
            'payment_status'       => $salesInvoice->payment_status,
            'payment_status_label' => $salesInvoice->payment_status_label,
            'payment_status_badge' => $salesInvoice->payment_status_badge,
            'total_paid'           => $salesInvoice->total_paid,
            'pending_amount'       => $salesInvoice->pending_amount,
        ], Response::HTTP_ACCEPTED);
    }

    private function buyerRelation(array $additionalColumns = []): string
    {
        $columns = $this->getBuyerColumns($additionalColumns);
        if (empty($columns)) {
            return 'buyer';
        }

        return 'buyer:' . implode(',', $columns);
    }

    private function getBuyerColumns(array $additional = []): array
    {
        static $cache = [];

        $desired = array_values(array_unique(array_merge(['id', 'display_name'], $additional)));
        $cacheKey = implode('|', $desired);
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $table = (new Buyer())->getTable();
        $available = [];
        foreach ($desired as $column) {
            if (Schema::hasColumn($table, $column)) {
                $available[] = $column;
            }
        }

        return $cache[$cacheKey] = $available;
    }

    private function updateInvoicePaymentStatuses()
    {
        SalesInvoice::with('payments')->chunk(100, function ($invoices) {
            foreach ($invoices as $invoice) {
                $totalPaid = $invoice->payments->where('tran_type', 'receive')->sum('amount');
                $grandTotal = $invoice->grand_total;

                $newStatus = 'pending';
                if ($totalPaid > 0) {
                    $newStatus = $totalPaid >= $grandTotal ? 'paid' : 'partial';
                }

                if ($invoice->payment_status !== $newStatus) {
                    $invoice->timestamps = false;
                    $invoice->update(['payment_status' => $newStatus]);
                    $invoice->timestamps = true;
                }
            }
        });
    }
}
