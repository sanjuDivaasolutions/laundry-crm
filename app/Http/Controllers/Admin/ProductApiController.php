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
 *  *  Last modified: 06/02/25, 7:44 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\FormRequests\ProductInventoryRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\Admin\ProductInventoryResource;
use App\Http\Resources\Admin\ProductListResourceCollection;
use App\Http\Resources\Admin\ProductResource;
use App\Models\InwardItem;
use App\Models\Product;
use App\Models\ProductFeature;
use App\Models\ProductInventory;
use App\Models\ProductOpening;
use App\Models\ProductOpeningShelves;
use App\Models\ProductPrice;
use App\Models\SalesInvoiceItem;
use App\Models\Shelf;
use App\Models\Unit;
use App\Services\CompanyService;
use App\Services\DatabaseService;
use App\Services\InventoryService;
use App\Services\ProductService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ProductApiController extends Controller
{
    protected $className = Product::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ProductResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = null;
    protected $pdfFilePrefix = null;
    protected $fields = ['name', 'sku'];
    protected $filters = [
        ['request' => 'f_current_company_only', 'field' => 'company', 'operator' => 'scope'],
    ];

    use SearchFilters;
    use ControllerRequest;
    use ExportRequest;

    public function index()
    {
        abort_if(Gate::denies('product_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //ProductService::fixOpeningStockAcrossAllProducts();

        return $this->getList();
    }

    public function getList()
    {
        /*$products = Product::query()
            ->onlyProducts()
            ->get();

        foreach ($products as $product) {
            ProductService::fixOpeningStock($product);
            ProductService::fixPrices($product);
        }*/

        $result = Product::query()
            ->onlyProducts()
            ->with(['category:id,name', 'stock:id,product_id,on_hand,in_transit']);

        return new ProductListResourceCollection($result->advancedFilter());
    }

    /**
     * @throws \Exception
     */
    public function store(StoreProductRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Product::query()->create($request->validated());
            $this->updateRelatives($request, $obj);
        });
        return (new ProductResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('product_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $unit = Unit::query()
            ->select('id', 'name')
            ->find(config('system.defaults.unit.id', 1));

        $defaults = [
            'company' => CompanyService::getDefaultCompanyEntry(),
            'unit_01' => $unit,
            'unit_02' => $unit,
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Product $product)
    {
        abort_if(Gate::denies('product_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        //InventoryService::updateProductStockAcrossAllWarehouses($product->id);

        $product->load([
            'unit_01:id,name',
            'unit_02:id,name',
            'category:id,name',
            'features.feature:id,name',
            'supplier:id,name',
            'prices.unit:id,name',
            'stock:id,product_id,warehouse_id,on_hand,in_transit',
            'stock.warehouse:id,name',
            'stock.shelves' => function ($q) {
                $q->select('product_stock_id', 'shelf_id', 'on_hand', 'in_transit');
                $q->where(function ($q1) {
                    $q1->where('on_hand', '!=', 0);
                    $q1->orWhere('in_transit', '!=', 0);
                });
            },
            'stock.shelves.shelf:id,name',
        ]);

        $inTransitTotal = 0;
        $onHandTotal = 0;
        $product->stock->each(function ($stock) use (&$inTransitTotal, &$onHandTotal) {
            $inTransitTotal += $stock->in_transit;
            $onHandTotal += $stock->on_hand;
        });
        $product->in_transit_total = $inTransitTotal;
        $product->on_hand_total = $onHandTotal;

        return new ProductResource($product);
    }

    /**
     * @throws \Exception
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        DatabaseService::executeTransaction(function () use ($request, $product) {
            $product->update($request->validated());
            $this->updateRelatives($request, $product);
        });
        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Product $product)
    {
        abort_if(Gate::denies('product_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ProductService::fixOpeningStock($product);
        ProductService::fixPrices($product);

        $product->load([
            'unit_01:id,name',
            'unit_02:id,name',
            'category:id,name',
            'company:id,name',
            'features.feature:id,name',
            'supplier:id,name',
            'prices.unit:id,name',
            'opening:id,product_id,warehouse_id,opening_stock,opening_stock_value',
            'opening.warehouse:id,name',
            'opening.shelves:id,product_opening_id,shelf_id,quantity',
            'opening.shelves.shelf:id,name',
        ]);

        return response([
            'data' => new ProductResource($product),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Product $product)
    {
        abort_if(Gate::denies('product_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        
        ProductService::remove($product);
        

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateRelatives($request, $obj)
    {
        $this->updateFeatures($request, $obj);
        $this->updatePrices($request, $obj);
        $this->updateStock($request, $obj);
    }

    private function updateFeatures($request, $obj)
    {
        $items = $request->input('features', []);
        foreach ($items as &$i) {
            $i['feature_id'] = $i['feature']['id'];
            $i['product_id'] = $obj->id;
            unset($i['feature']);
        }
        $request->merge(['features' => $items]);
        $this->updateChild($request, $obj, 'features', ProductFeature::class, 'features', 'product_id');
    }

    private function updatePrices($request, $obj)
    {
        $items = $request->input('prices', []);
        foreach ($items as &$i) {
            $i['unit_id'] = $i['unit']['id'];
            $i['product_id'] = $obj->id;
            unset($i['unit']);
        }
        $request->merge(['prices' => $items]);
        $this->updateChild($request, $obj, 'prices', ProductPrice::class, 'prices', 'product_id');
    }

    private function updateStock($request, $obj)
    {
        $openingItems = ProductInventoryRequest::prepareOpening($request->input('opening', []), $obj);
        $request->merge(['opening' => $openingItems]);

        $subItems = [
            [
                'field'        => 'shelves',
                'model'        => ProductOpeningShelves::class,
                'relation'     => 'shelves',
                'update_field' => 'product_opening_id',
            ],
        ];

        $this->updateChild($request, $obj, 'opening', ProductOpening::class, 'opening', 'product_id', [], $subItems);
        $this->updateOpeningStock($obj);
        InventoryService::updateProductStockAcrossAllWarehouses($obj->id);
    }

    public function fixOpeningStock(Product $product)
    {
        $this->updateOpeningStock($product);
        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updateOpeningStock($obj)
    {
        $userId = auth()->id();
        $shelves = Shelf::query()->get();
        $inventories = ProductInventory::query()
            ->where('product_id', $obj->id)
            ->get();
        $existingInventoryIds = $inventories->pluck('id')->toArray();
        $updatedInventoryIds = [];
        $openings = ProductOpening::query()
            ->where('product_id', $obj->id)
            ->with(['shelves'])
            ->get();

        $defaultOpeningStockDate = config('system.defaults.opening_stock_date', now()->format(config('project.date_format')));

        foreach ($shelves as $shelf) {
            $i = $inventories->where('shelf_id', $shelf->id)->first();
            $opening = $openings->pluck('shelves')->flatten()->where('shelf_id', $shelf->id)->first();

            $quantity = $opening ? $opening->quantity : 0;
            $rate = $openings->sum('opening_stock_value') / max(1, $quantity);
            $amount = $quantity * $rate;

            if (!$i) {
                $i = new ProductInventory();
                $i->product_id = $obj->id;
                $i->warehouse_id = $shelf->warehouse_id;
                $i->shelf_id = $shelf->id;
                $i->reason = 'opening';
                $i->user_id = $userId;
                $i->date = $defaultOpeningStockDate;
            }
            $i->quantity = $quantity;
            $i->rate = $rate;
            $i->amount = $amount;
            $i->save();
            $updatedInventoryIds[] = $i->id;
        }

        // get difference between existing and updated inventory ids
        $diff = array_diff($existingInventoryIds, $updatedInventoryIds);

        // delete the difference
        ProductInventory::query()
            ->where('reason', 'opening')
            ->whereIn('id', $diff)
            ->delete();

    }

    public function getInventoryList(Product $product)
    {
        $data = ProductInventory::query()
            ->where('product_id', $product->id)
            ->orderBy('date', 'desc')
            /*->with([
                'inventoryable' => function ($query) {
                    $query->select('id'); // Ensures at least the ID is selected
                },
                'inventoryable.salesInvoice',
                'inventoryable.InwardItem',
            ])*/
            ->paginate(200);
        $balance = 0;
        $data->map(function ($item) use (&$balance) {
            $balance += $item->quantity;
            $item->order_number = $this->getOrderNumber($item);
            $item->balance = $balance;
            return $item;
        });

        return response([
            'data' => ProductInventoryResource::collection($data),
            'meta' => [],
        ]);

    }

    private function getOrderNumber($item)
    {
        return match ($item->inventoryable_type) {
            SalesInvoiceItem::class => $item->inventoryable->salesInvoice->invoice_number,
            InwardItem::class => $item->inventoryable->inward->invoice_number,
            default => '',
        };
    }

}
