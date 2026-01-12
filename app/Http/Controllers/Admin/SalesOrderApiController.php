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
 *  *  Last modified: 16/01/25, 10:54 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Http\Resources\Admin\SalesOrderListResourceCollection;
use App\Http\Resources\Admin\SalesOrderResource;
use App\Models\Currency;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderActivity;
use App\Models\SalesOrderItem;
use App\Services\CompanyService;
use App\Services\DatabaseService;
use App\Services\OrderService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SalesOrderApiController extends Controller
{
    use ControllerRequest;
    use ExportRequest;
    use SearchFilters;

    protected string $className = SalesOrder::class;
    protected array $scopes = [];
    protected array $with = [];
    protected string $fetcher = 'advancedFilter';
    protected array $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected array $fields = ['so_number'];
    protected array $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    public function __construct(protected OrderService $orderService)
    {
        // Constructor injection for OrderService
    }

    public function index()
    {
        abort_if(Gate::denies('sales_order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    private function getList()
    {
        return new SalesOrderListResourceCollection(
            SalesOrder::query()
                ->with([
                    'company:id,name',
                    'buyer:id,display_name',
                    'user:id,name',
                ])
                ->advancedFilter()
        );
    }

    public function store(StoreSalesOrderRequest $request)
    {
        $salesOrder = null;
        DatabaseService::executeTransaction(function () use ($request, &$salesOrder) {
            $salesOrder = SalesOrder::create($request->validated());
            $this->updateRelatives($request, $salesOrder);
            
            // Commission calculation is handled by OrderService logic if embedded, 
            // but here we might relying on Observers or simple create. 
            // If we strictly want to use OrderService::createSalesOrder, strict refactoring is needed.
            // For now, adhering to existing pattern but cleaning up syntax.
            if ($salesOrder->agent_id) {
                // If we want to use the service for commissions:
                // $this->orderService->recalculateCommission($salesOrder);
            }
        });

        return (new SalesOrderResource($salesOrder))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('sales_order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $currency = Currency::query()->where('code', config('system.defaults.currency.code'))->select(['id', 'name', 'code', 'rate'])->first();

        $defaults = [
            'date'          => now()->format(config('project.date_format')),
            'company'       => CompanyService::getDefaultCompanyEntry(),
            'currency'      => $currency,
            'currency_rate' => $currency->rate,
        ];
        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(SalesOrder $salesOrder)
    {
        abort_if(Gate::denies('sales_order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new SalesOrderResource($salesOrder->load([
            'buyer.billingAddress.city:id,name',
            'paymentTerm:id,name',
            'items.product:id,name',
            'items.unit:id,name',
        ]));
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder)
    {
        DatabaseService::executeTransaction(function () use ($request, &$salesOrder) {
            $salesOrder->update($request->validated());
            $this->updateRelatives($request, $salesOrder);
        });
        return (new SalesOrderResource($salesOrder))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(SalesOrder $salesOrder)
    {
        abort_if(Gate::denies('sales_order_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new SalesOrderResource($salesOrder->load([
                'company:id,name',
                'buyer:id,display_name',
                'items:id,sales_order_id,unit_id,product_id,rate,quantity,amount',
                'items.product:id,name',
                'items.unit:id,name',
                'warehouse:id,name',
                'paymentTerm:id,name',
            ])),
            'meta' => [],
        ]);
    }

    public function destroy(SalesOrder $salesOrder)
    {
        abort_if(Gate::denies('sales_order_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($salesOrder) {
            $salesOrder->items()->each(function ($item) {
                $item->delete();
            });
            $salesOrder->delete();
        });
        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function convertToInvoice(SalesOrder $salesOrder)
    {
        $invoice = $this->orderService->convertToInvoice($salesOrder);

        return response()->json([
            'message' => 'Order converted successfully',
            'invoice_id' => $invoice->id
        ]);
    }

    public function updateStatus(SalesOrder $salesOrder)
    {
        // Restored business logic from previous private updateStatus method
        $items = $salesOrder->items()->get();

        $userId = auth()->id();

        foreach ($items as $item) {
            $status = [
                'sales_order_id'         => $salesOrder->id,
                'sales_order_item_id'    => $item->id,
                'user_id'                => $userId,
                'fir_activity_master_id' => config('system.defaults.fir_activity.pending', 1),
                'active'                 => 1,
                'date'                   => now()->format(config('project.date_format')),
                'remark'                 => request('remark'), // Added remark from request support
            ];
            SalesOrderActivity::create($status);
        }
        
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function getOrderStatistics()
    {
         // Logic delegated to OrderService
         $company = CompanyService::getDefaultCompanyEntry(); // Assuming default company context
         // If company is in request, use that
         if (request('company_id')) {
             // We would need to load the company model
             $company = \App\Models\Company::find(request('company_id')) ?? $company;
         }
         
         if (!$company) {
             return response()->json(['stats' => []]);
         }

         $stats = $this->orderService->getOrderStatistics($company, request('start_date'), request('end_date'));
         
         return response()->json(['stats' => $stats]);
    }


    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTotals($obj);
    }

    private function updateDiscounts($request, $obj)
    {
        //$this->updateChild($request, $obj, 'discounts', InvoiceDiscount::class);
    }

    private function updateTotals($obj)
    {
        OrderService::updateSalesOrderTotals($obj);
    }

    private function updateItems($request, $obj)
    {
        $items = stringToArray($request->input('items', []));
        foreach ($items as &$i) {
            $p = Product::query()->find($i['product']['id']);
            if ($p) {
                $p = $p->toArray();
                $i['product_id'] = $i['product']['id'];
                unset($p['rate'], $p['unit_id'], $p['id']);
                $i = array_merge($i, $p);
                unset($i['product']);
            }
            if (isset($i['unit']) && $i['unit']) {
                $i['unit_id'] = $i['unit']['id'];
                unset($i['unit']);
            }

        }

        $request->merge(['items' => $items]);
        $this->updateChild($request, $obj, 'items', SalesOrderItem::class, 'items', 'sales_order_id');
    }

    public function getSinglePdf(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'company',
            'buyer.billingAddress.city:id,name',
            'paymentTerm:id,name',
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $code = $salesOrder->so_number;
        $fileName = "sales-order-$code.pdf";

        $compact = [
            'obj' => $salesOrder,
        ];

        $pdf = Pdf::loadView('pdf-templates.sales-order.sales-order', $compact);
        return $pdf->download($fileName);
    }

    private function cleanPo($obj)
    {

        $nullFields = ['id', 'po_number'];

        foreach ($nullFields as $nf) {
            $obj[$nf] = null;
        }
        if (isset($obj['items'])) {
            foreach ($obj['items'] as &$i) {
                $i['id'] = null;
                $i['sales_order_id'] = null;
                unset($i['purchase_order_id']);
            }
        }
        $obj['date'] = now()->format(config('project.date_format'));
        $obj['estimated_shipment_date'] = now()->format(config('project.date_format'));

        return $obj;
    }

    public function getSoPackage(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'items.product:id,name',
            'items.unit:id,name',
        ]);

        $obj = [
            'items' => $salesOrder->items->map(function ($item) {
                return [
                    'product'         => $item->product,
                    'unit'            => $item->unit,
                    'order_quantity'  => $item->quantity,
                    'packed_quantity' => 0,
                    'quantity'        => 0,
                ];
            })->toArray()
        ];

        return $obj;
    }

    public function getSoInvoice(SalesOrder $salesOrder)
    {
        $salesOrder->load([
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $obj = [
            'buyer' => $salesOrder->buyer,
            'items' => $salesOrder->items->map(function ($item) {
                return [
                    'product'  => $item->product,
                    'unit'     => $item->unit,
                    'quantity' => $item->quantity,
                    'amount'   => $item->amount,
                    'rate'     => $item->rate,
                ];
            })
        ];
        return $obj;

    }

 
}
