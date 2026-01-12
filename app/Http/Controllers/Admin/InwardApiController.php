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
 *  *  Last modified: 05/02/25, 6:29 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InwardRequest;
use App\Http\Resources\Admin\InwardListResourceCollection;
use App\Http\Resources\Admin\InwardResource;
use App\Models\Inward;
use App\Models\InwardItem;
use App\Models\InwardItemShelf;
use App\Models\Warehouse;
use App\Services\CanadaTaxService;
use App\Services\CompanyService;
use App\Services\DatabaseService;
use App\Services\InventoryService;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Gate;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class InwardApiController extends Controller
{
    protected $className = Inward::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = InwardResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'inwards-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['invoice_number'];
    protected $filters = [
        ['request' => 'f_date_range', 'field' => 'date', 'operator' => 'date_range', 'separator' => ' to '],
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('inward_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return new InwardListResourceCollection(Inward::query()
            ->with('company:id,name', 'warehouse:id,name', 'supplier:id,name,display_name', 'user:id,name')
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(InwardRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Inward::query()->create($request->validated());
            $this->updateRelatives($request, $obj);
        });
        return (new InwardResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('inward_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaultWarehouseId = config('system.defaults.warehouse.id', 1);

        $company = CompanyService::getDefaultCompanyEntry();
        $warehouse = $company ? $company->warehouse : Warehouse::find($defaultWarehouseId);
        unset($company->warehouse);

        $defaults = [
            'date'      => Carbon::now()->format(config('project.date_format')),
            'company'   => $company,
            'warehouse' => $warehouse,
            'state'     => CanadaTaxService::getDefaultStateObject(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Inward $inward)
    {
        abort_if(Gate::denies('inward_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inward->load([
            'company:id,name',
            'warehouse:id,name',
            'supplier:id,name,display_name',
            'items.product:id,name',
            'items.unit:id,name',
            'taxes:id,amount,tax_rate_id,taxable_type,taxable_id',
            'taxes.taxRate:id,name,rate',
        ]);

        return new InwardResource($inward);
    }

    /**
     * @throws \Exception
     */
    public function update(InwardRequest $request, Inward $inward)
    {
        DatabaseService::executeTransaction(function () use ($request, $inward) {
            $inward->update($request->validated());
            $this->updateRelatives($request, $inward);
        });

        return (new InwardResource($inward))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Inward $inward)
    {
        abort_if(Gate::denies('inward_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inward->load([
            'company:id,name',
            'state:id,name',
            'warehouse:id,name',
            'supplier:id,name,display_name',
            'items.product:id,name',
            'items.unit:id,name',
            'items.inwardItemShelf.shelf:id,name',
        ]);

        $inward->items->map(function ($item) {
            $firstShelf = $item->inwardItemShelf->first();
            $item->shelf = $firstShelf ? $firstShelf->shelf : [];
            if ($item->shelf) {
                $item->shelf->parent_id = $firstShelf ? $firstShelf->id : null;
            }
            return $item;
        });

        return response([
            'data' => new InwardResource($inward),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Inward $inward)
    {
        abort_if(Gate::denies('inward_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($inward) {

            $items = $inward->items()->get();
            // save product id in a variable and then delete the item and then update stock
            foreach ($items as $item) {
                $product_id = $item->product_id;
                $item->inventory()->delete();
                $item->inwardItemShelf()->delete();
                $item->delete();
                InventoryService::updateProductStockInWarehouse($product_id, $inward->warehouse_id);
            }

            $inward->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function getSinglePdf(Inward $inward)
    {
        $inward->load([
            'company:id,name',
            'warehouse:id,name',
            'supplier:id,name,display_name,billing_address_id',
            'supplier.billingAddress',
            'supplier.billingAddress.city',
            'supplier.billingAddress.state',
            'items.product:id,name',
            'items.unit:id,name',
        ]);
        $code = $inward->invoice_number;
        $fileName = "inward-$code.pdf";

        $compact = [
            'obj' => $inward,
        ];

        $pdf = Pdf::loadView('pdf-templates.inward.inward', $compact);
        return $pdf->download($fileName);
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
        $obj->load('items.firstInwardItemShelf');
        //Setting first shelf id from InwardItemShelf to match with SalesInvoiceItem
        $obj->items->map(function ($item) {
            $item->shelf_id = $item->firstInwardItemShelf->shelf_id;
            return $item;
        });
        InventoryService::updateStockBasedOnOrder($obj, 'purchase');
    }

    private function updateTaxes($request, $obj)
    {
        InvoiceService::updateTaxes($request, $obj, Inward::class);
    }

    private function updateTotals($obj)
    {
        OrderService::updateSalesOrderTotals($obj, 'inward');
    }

    private function updateItems($request, $obj)
    {
        $subItems = [
            [
                'field'        => 'shelf',
                'model'        => InwardItemShelf::class,
                'relation'     => 'inwardItemShelf',
                'update_field' => 'inward_item_id',
            ],
        ];
        $this->updateChild($request, $obj, 'items', InwardItem::class, 'items', 'inward_id', [], $subItems);
    }
}
