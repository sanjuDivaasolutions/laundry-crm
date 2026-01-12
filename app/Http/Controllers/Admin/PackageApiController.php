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
 *  *  Last modified: 24/01/25, 5:05 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Http\Resources\Admin\PackageResource;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\DatabaseService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class PackageApiController extends Controller
{
    protected $className = Package::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = PackageResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['invoice_number', 'reference_no'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('package_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        $packages = Package::query()
            ->with([
                'salesInvoice:id,invoice_number',
                'items:id,package_id,quantity',
            ])
            ->reorder()
            ->latest()
            ->advancedFilter();

        $packages->map(function ($package) {
            $package->total_quantity = $package->items->sum('quantity');
        });

        return PackageResource::collection($packages);
    }

    /**
     * @throws \Exception
     */
    public function store(PackageRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request) {
            $obj = Package::create($request->validated());
            $this->updateRelatives($request, $obj);
        });

        return (new PackageResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('package_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaults = [
            'date' => Carbon::now()->format(config('project.date_format')),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults
        ]);
    }

    public function show(Package $package)
    {
        abort_if(Gate::denies('package_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $package->load([
            'salesInvoice:id,buyer_id,invoice_number,reference_no',
            'salesInvoice.buyer:id,name,billing_address_id',
            'salesInvoice.buyer.billingAddress.city:id,name',
            'salesInvoice.buyer.billingAddress.state:id,name',
            'items:id,package_id,product_id,unit_id,quantity',
            'items.product:id,name',
            'items.unit:id,name',
        ]);

        $totalQuantity = 0;
        $package->items->map(function ($item) use (&$totalQuantity) {
            $totalQuantity += $item->quantity;
            $item->quantity = round($item->quantity);
        });
        $package->total_quantity = $totalQuantity;

        return new PackageResource($package);
    }

    /**
     * @throws \Exception
     */
    public function update(PackageRequest $request, Package $package)
    {
        DatabaseService::executeTransaction(function () use ($package, $request) {
            $package->update($request->validated());
            $this->updateRelatives($request, $package);
        });
        return (new PackageResource($package))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Package $package)
    {
        abort_if(Gate::denies('package_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'data' => new PackageResource($package->load([
                'salesOrder:id,so_number',
                'items:id,package_id,unit_id,product_id,quantity',
                'items.product:id,name',
                'items.unit:id,name',
            ])),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Package $package)
    {
        abort_if(Gate::denies('package_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($package) {
            $package->items()->delete();
            $package->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateItems($request, $obj);
    }

    private function updateItems($request, $obj)
    {
        $items = $request->input('items', []);
        foreach ($items as &$item) {
            $item['package_id'] = $obj->id;
        }
        //dd($items);
        $request->merge(['items' => $items]);
        $this->updateChild($request, $obj, 'items', PackageItem::class, 'items', 'package_id');
    }

    public function getSalesInvoiceItems(SalesInvoice $salesInvoice)
    {
        $siId = $salesInvoice->id;

        $items = SalesInvoiceItem::query()
            ->where('sales_invoice_id', $siId)
            ->with([
                'product:id,name,sku',
                'unit:id,name',
            ])
            ->get();

        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'name'                  => $item->product->name,
                'sku'                   => $item->product->sku,
                'quantity'              => $item->quantity,
                'boxes'                 => $item->boxes,
                'product'               => $item->product,
                'unit'                  => $item->unit,
                'sales_invoice_item_id' => $item->id,
                'product_id'            => $item->product_id,
                'unit_id'               => $item->unit_id,
            ];
        }

        return response(['items' => $result], Response::HTTP_OK);
    }

    public function getSinglePdf(Package $package)
    {
        $package->load([
            'salesInvoice.company',
            'salesInvoice.buyer:id,name,billing_address_id,shipping_address_id',
            'salesInvoice.buyer.billingAddress:id,name,address_1,address_2,city_id,state_id,postal_code,phone',
            'salesInvoice.buyer.billingAddress.city:id,name',
            'items' => function ($q) {
                $q->groupBy('product_id');
                $q->selectRaw('*, SUM(quantity) as total_quantity');
            },
            'items.product:id,name',
            'items.unit:id,name',
        ]);

        $totalQuantity = 0;
        $package->items->map(function ($item) use (&$totalQuantity) {
            $totalQuantity += $item->total_quantity;
            $item->quantity = round($item->total_quantity);
        });
        $package->total_quantity = $totalQuantity;

        $code = $package->code;
        $fileName = "packing-slip-$code.pdf";
        $compact = [
            'obj' => $package,
        ];

        $pdf = Pdf::loadView('pdf-templates.package.package', $compact);
        return $pdf->download($fileName);
    }
}
