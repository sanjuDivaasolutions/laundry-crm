<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 16/10/24, 5:36 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Http\Resources\Admin\PurchaseOrderListResource;
use App\Http\Resources\Admin\PurchaseOrderResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\ProductService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Response;

class PurchaseOrderApiController extends Controller
{
    protected $className = PurchaseOrder::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PurchaseOrderResource::class;
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
        abort_if(Gate::denies('purchase_order_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return PurchaseOrderListResource::collection(
            PurchaseOrder::query()
                ->with([
                    'supplier:id,display_name',
                    'company:id,name',
                    'user:id,name',
                ])
                ->advancedFilter()
        );
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        $purchaseOrder = PurchaseOrder::create($request->validated());

        $this->updateRelatives($request, $purchaseOrder);

        return (new PurchaseOrderResource($purchaseOrder))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('purchase_order_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta'     => [],
            'defaults' => [
                'date' => Carbon::now()->format(config('project.date_format')),
            ],
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        abort_if(Gate::denies('purchase_order_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PurchaseOrderResource($purchaseOrder->load([
            'warehouse:id,name',
            'supplier:id,display_name,billing_address_id',
            'supplier.billingAddress:id,address_1,address_2,postal_code,name',
            'items:id,purchase_order_id,unit_id,product_id,rate,quantity,amount',
            'items.product:id,name',
            'items.unit:id,name',
            'paymentTerm:id,name',
            'shipmentMode:id,name',
        ]));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->validated());
        $this->updateRelatives($request, $purchaseOrder);

        return (new PurchaseOrderResource($purchaseOrder))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort_if(Gate::denies('purchase_order_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PurchaseOrderResource($purchaseOrder->load([
                'company:id,name',
                'warehouse:id,name',
                'supplier:id,display_name',
                'items:id,purchase_order_id,unit_id,product_id,rate,quantity,amount',
                'items.product:id,name',
                'items.unit:id,name',
                'paymentTerm:id,name',
                'shipmentMode:id,name',
            ])),
            'meta' => [],
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort_if(Gate::denies('purchase_order_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $purchaseOrder->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTotals($obj);
        // $this->updateStocks($obj);
    }

    public function updateStocks($obj)
    {
        ProductService::updateStockOrder($obj, 'purchase');
    }

    private function updateTotals($obj)
    {

        $subTotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;
        $taxRate = $obj->tax_rate;

        $items = $obj->items()->get();
        foreach ($items as $item) {
            $subTotal += $item->amount;
        }


        /*$discounts = InvoiceDiscount::query()->where('invoice_id',$obj->id)->get();
        foreach ($discounts as $d) {
            $discountTotal += ($mixTotal * $d['rate']) / 100;
        }*/

        /*$taxes = InvoiceTax::query()->where('invoice_id',$obj->id)->get();
        foreach ($taxes as $t) {
            $taxTotal += ($mixTotal * $t['rate']) / 100;
        }*/

        $taxTotal = $subTotal - ($subTotal * 100) / (100 + $taxRate);

        $subTotal = $subTotal - round($taxTotal, 2);
        $grandTotal = $subTotal + $taxTotal;


        $obj->sub_total = $subTotal;
        $obj->discount_total = $discountTotal;
        $obj->tax_total = $taxTotal;
        $obj->grand_total = $grandTotal;
        $obj->save();
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
        $this->updateChild($request, $obj, 'items', PurchaseOrderItem::class, 'items', 'purchase_order_id');
    }

    public function getSinglePdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier.billingAddress.city:id,name',
            'paymentTerm:id,name',
            'shipmentMode:id,name',
            'items:id,purchase_order_id,unit_id,product_id,rate,quantity,amount',
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $code = $purchaseOrder->po_number;
        $fileName = "purchase-order-$code.pdf";

        $compact = [
            'obj' => $purchaseOrder,
        ];

        $pdf = Pdf::loadView('pdf-templates.purchase-order.purchase-order', $compact);
        return $pdf->download($fileName);
    }

    public function getPoInvoice(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $obj = [
            'supplier' => $purchaseOrder->supplier,
            'items'    => $purchaseOrder->items->map(function ($item) {
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
