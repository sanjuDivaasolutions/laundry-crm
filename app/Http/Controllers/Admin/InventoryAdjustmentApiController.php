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
 *  *  Last modified: 05/02/25, 6:50 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryAdjustmentRequest;
use App\Http\Requests\StoreInventoryAdjustmentRequest;
use App\Http\Requests\UpdateInventoryAdjustmentRequest;
use App\Http\Resources\Admin\InventoryAdjustmentResource;
use App\Models\InventoryAdjustment;
use App\Services\DatabaseService;
use App\Services\InventoryService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class InventoryAdjustmentApiController extends Controller
{
    protected $className = InventoryAdjustment::class;
    protected $scopes = [];
    protected $with = ['user:id,name', 'product:id,name', 'shelf:id,name', 'targetShelf:id,name'];
    protected $exportResource = InventoryAdjustmentResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'inventory-adjustments-list-';
    protected $pdfFilePrefix = null;
    protected $fields = ['name'];
    protected $filters = [
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('inventory_adjustment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return InventoryAdjustmentResource::collection(InventoryAdjustment::query()
            ->with($this->with)
            ->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(InventoryAdjustmentRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = InventoryAdjustment::query()->create($request->validated());
            $this->updateRelatives($request, $obj);
        });

        return (new InventoryAdjustmentResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('inventory_adjustment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response([
            'meta' => [],
        ]);
    }

    public function show(InventoryAdjustment $inventoryAdjustment)
    {
        abort_if(Gate::denies('inventory_adjustment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new InventoryAdjustmentResource($inventoryAdjustment);
    }

    /**
     * @throws \Exception
     */
    public function update(InventoryAdjustmentRequest $request, InventoryAdjustment $inventoryAdjustment)
    {
        DatabaseService::executeTransaction(function () use ($request, $inventoryAdjustment) {
            $inventoryAdjustment->update($request->validated());
            $this->updateRelatives($request, $inventoryAdjustment);
        });

        return (new InventoryAdjustmentResource($inventoryAdjustment))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(InventoryAdjustment $inventoryAdjustment)
    {
        abort_if(Gate::denies('inventory_adjustment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $inventoryAdjustment->load($this->with);
        $reason = $inventoryAdjustment->reason;
        $reasonObject = collect(InventoryAdjustment::REASON_SELECT)->firstWhere('value', $reason);
        $inventoryAdjustment->reason = $reasonObject ?: null;

        return response([
            'data' => new InventoryAdjustmentResource($inventoryAdjustment),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(InventoryAdjustment $inventoryAdjustment)
    {
        abort_if(Gate::denies('inventory_adjustment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($inventoryAdjustment) {
            $inventory = $inventoryAdjustment->inventory()->get();
            $inventory->each(function ($i) use ($inventoryAdjustment) {
                $product_id = $i->product_id;
                $warehouse_id = $i->warehouse_id;
                $i->delete();
                InventoryService::updateProductStockInWarehouse($product_id, $warehouse_id);
            });
            $inventoryAdjustment->delete();
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        // update relatives
        $this->updateStock($obj);
    }

    private function updateStock($obj)
    {
        InventoryService::updateAdjustmentInventoryStock($obj);
    }
}
