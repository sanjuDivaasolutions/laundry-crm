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
 *  *  Last modified: 09/01/25, 5:26 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\Admin\ServiceResource;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\CompanyService;
use App\Services\DatabaseService;
use App\Services\ProductService;
use App\Traits\ControllerRequest;
use App\Traits\ExportRequest;
use App\Traits\SearchFilters;
use Gate;
use Illuminate\Http\Response;

class ServiceApiController extends Controller
{
    protected $className = Product::class;
    protected $scopes = [];
    protected $with = [];
    protected $exportResource = ServiceResource::class;
    protected $fetcher = 'advancedFilter';
    protected $processListMethod = 'getProcessedList';
    protected $filterMethods = ['index', 'getCsv', 'getPdf'];
    protected $csvFilePrefix = 'services-list-';
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
        abort_if(Gate::denies('service_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return $this->getList();
    }

    public function getList()
    {
        return ServiceResource::collection(Product::query()
            ->onlyServices()
            ->with(['category:id,name', 'prices:id,product_id,sale_price'])
            ->advancedFilter());
    }

    public function store(StoreServiceRequest $request)
    {
        $obj = null;
        DatabaseService::executeTransaction(function () use ($request, &$obj) {
            $obj = Product::query()->create($request->validated());
            $this->updatePrice($obj, $request);
        });

        return (new ServiceResource($obj))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function create()
    {
        abort_if(Gate::denies('service_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $defaults = [
            'company' => CompanyService::getDefaultCompanyEntry(),
        ];

        return response([
            'meta'     => [],
            'defaults' => $defaults,
        ]);
    }

    public function show(Product $product)
    {
        abort_if(Gate::denies('service_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new ServiceResource($product);
    }

    public function update(UpdateServiceRequest $request, Product $product)
    {
        DatabaseService::executeTransaction(function () use ($request, $product) {
            $product->update($request->validated());
            $this->updatePrice($product, $request);
        });

        return (new ServiceResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function edit(Product $product)
    {
        abort_if(Gate::denies('service_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $product->load([
            'prices',
            'category:id,name',
            'company:id,name',
        ]);

        $firstPrice = $product->prices->first();
        $product->sale_price = $firstPrice ? $firstPrice->sale_price : 0;

        return response([
            'data' => new ServiceResource($product),
            'meta' => [],
        ]);
    }

    /**
     * @throws \Exception
     */
    public function destroy(Product $product)
    {
        abort_if(Gate::denies('service_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        DatabaseService::executeTransaction(function () use ($product) {
            ProductService::remove($product);
        });

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function updatePrice($product, $request)
    {
        $prices = $product->prices()->get();
        $price = $prices->first();

        if (!$price) {
            $price = new ProductPrice();
        }

        $price->product_id = $product->id;
        $price->sale_price = $request->sale_price;
        $price->purchase_price = 0;
        $price->lowest_sale_price = 0;
        $price->unit_id = 1;
        $price->save();

    }
}
