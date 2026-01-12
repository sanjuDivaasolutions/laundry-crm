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
 *  *  Last modified: 16/10/24, 5:35 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseInvoiceRequest;
use App\Http\Requests\UpdatePurchaseInvoiceRequest;
use App\Http\Resources\Admin\PurchaseInvoiceListResource;
use App\Http\Resources\Admin\PurchaseInvoiceResource;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Services\ProductService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Gate;
use Illuminate\Http\Response;

class PurchaseInvoiceApiController extends Controller
{
    protected $className = PurchaseInvoice::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PurchaseInvoiceResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['supplier.name'];
    protected $filters = [
        //['request'=>'','field'=>'','operator'=>'in'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('purchase_invoice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return PurchaseInvoiceListResource::collection(
            PurchaseInvoice::query()
                ->with([
                    'company:id,name',
                    'supplier:id,display_name',
                    'purchaseOrder:id,po_number',
                    'user:id,name',
                ])
                ->advancedFilter()
        );
    }

    public function store(StorePurchaseInvoiceRequest $request)
    {
        $purchaseInvoice = PurchaseInvoice::create($request->validated());
        $this->updateRelatives($request, $purchaseInvoice);

        return (new PurchaseInvoiceResource($purchaseInvoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('purchase_invoice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        abort_if(Gate::denies('purchase_invoice_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new PurchaseInvoiceResource($purchaseInvoice->load([
            'supplier.billingAddress.city:id,name',
            'purchaseOrder:id,po_number',
            'items:id,purchase_invoice_id,unit_id,product_id,rate,quantity,amount',
            'items.product:id,name',
            'items.unit:id,name',
        ]));
    }

    public function update(UpdatePurchaseInvoiceRequest $request, PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->update($request->validated());

        return (new PurchaseInvoiceResource($purchaseInvoice))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        abort_if(Gate::denies('purchase_invoice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PurchaseInvoiceResource($purchaseInvoice->load([
                'company:id,name',
                'supplier:id,display_name',
                'purchaseOrder:id,po_number',
                'items:id,purchase_invoice_id,unit_id,product_id,rate,quantity,amount',
                'items.product:id,name',
                'items.unit:id,name',
            ])),
            'meta' => [],
        ]);
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        abort_if(Gate::denies('purchase_invoice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $purchaseInvoice->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
        $this->updateTotals($obj);
        $this->updateStocks($obj);
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
        /*  $obj->discount_total = $discountTotal; */
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
                unset($p['rate'], $p['shade_id'], $p['sub_shade_id'], $p['unit_id'], $p['id']);
                $i = array_merge($i, $p);
                unset($i['product']);
            }
            if (isset($i['unit']) && $i['unit']) {
                $i['unit_id'] = $i['unit']['id'];
                unset($i['unit']);
            }

        }

        $request->merge(['items' => $items]);
        $this->updateChild($request, $obj, 'items', PurchaseInvoiceItem::class, 'items', 'purchase_invoice_id');
    }

    public function getSinglePdf(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load([
            'supplier.billingAddress.city:id,name',
            'items:id,purchase_invoice_id,unit_id,product_id,rate,quantity,amount',
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $code = $purchaseInvoice->invoice_number;
        $fileName = "purchase-invoice-$code.pdf";

        $compact = [
            'obj' => $purchaseInvoice,
        ];

        $pdf = Pdf::loadView('pdf-templates.purchase-invoice.purchase-invoice', $compact);
        return $pdf->download($fileName);
    }
}
