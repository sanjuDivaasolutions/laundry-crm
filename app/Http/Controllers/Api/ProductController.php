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
 *  *  Last modified: 07/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Product::with(['warehouse', 'rack', 'category', 'supplier', 'company']);

        // Apply filters
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('rack_id')) {
            $query->where('rack_id', $request->rack_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('hsn_code')) {
            $query->where('hsn_code', 'like', '%'.$request->hsn_code.'%');
        }

        if ($request->has('batch_number')) {
            $query->where('batch_number', 'like', '%'.$request->batch_number.'%');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('barcode', 'like', '%'.$search.'%')
                    ->orWhere('hsn_code', 'like', '%'.$search.'%')
                    ->orWhere('batch_number', 'like', '%'.$search.'%');
            });
        }

        // Apply advanced filtering if the trait exists
        if (method_exists(Product::class, 'filter')) {
            $query = $query->filter($request->all());
        }

        $products = $query->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return ProductResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products',
            'type' => 'required|in:product,service',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'barcode_type' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'company_id' => 'required|exists:companies,id',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'manufacturer' => 'nullable|string|max:255',
            'unit_01_id' => 'nullable|exists:units,id',
            'unit_02_id' => 'nullable|exists:units,id',
            'is_returnable' => 'boolean',
            'has_inventory' => 'boolean',
            'hsn_code' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'rack_id' => 'nullable|exists:racks,id',
        ]);

        // Validate that rack belongs to warehouse if both are provided
        if (! empty($validated['warehouse_id']) && ! empty($validated['rack_id'])) {
            $rack = Rack::find($validated['rack_id']);
            if ($rack && $rack->warehouse_id !== $validated['warehouse_id']) {
                return response()->json([
                    'message' => 'Selected rack does not belong to the selected warehouse.',
                    'errors' => [
                        'rack_id' => ['Rack must belong to the selected warehouse.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $product = Product::create($validated);

        return new ProductResource($product->load(['warehouse', 'rack', 'category', 'supplier', 'company']));
    }

    /**
     * Display the specified resource.
     *
     * @return ProductResource
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load([
            'warehouse',
            'rack',
            'category',
            'supplier',
            'company',
            'unit_01',
            'unit_02',
            'features',
            'prices',
            'stock',
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return ProductResource
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:products,code,'.$product->id,
            'type' => 'sometimes|required|in:product,service',
            'sku' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'barcode_type' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'company_id' => 'sometimes|required|exists:companies,id',
            'description' => 'nullable|string',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'manufacturer' => 'nullable|string|max:255',
            'unit_01_id' => 'nullable|exists:units,id',
            'unit_02_id' => 'nullable|exists:units,id',
            'is_returnable' => 'boolean',
            'has_inventory' => 'boolean',
            'hsn_code' => 'nullable|string|max:255',
            'batch_number' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'rack_id' => 'nullable|exists:racks,id',
        ]);

        // Validate that rack belongs to warehouse if both are provided
        if (! empty($validated['warehouse_id']) && ! empty($validated['rack_id'])) {
            $rack = Rack::find($validated['rack_id']);
            if ($rack && $rack->warehouse_id !== $validated['warehouse_id']) {
                return response()->json([
                    'message' => 'Selected rack does not belong to the selected warehouse.',
                    'errors' => [
                        'rack_id' => ['Rack must belong to the selected warehouse.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $product->update($validated);

        return new ProductResource($product->load(['warehouse', 'rack', 'category', 'supplier', 'company']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get warehouses for dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function warehouses()
    {
        $warehouses = Warehouse::active()
            ->select('id', 'name', 'code')
            ->get()
            ->map(function ($warehouse) {
                return [
                    'value' => $warehouse->id,
                    'label' => $warehouse->full_name ?? "{$warehouse->code} - {$warehouse->name}",
                ];
            });

        return response()->json($warehouses);
    }

    /**
     * Get racks for dropdown based on warehouse.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function racks(Request $request)
    {
        $query = Rack::active()->select('id', 'name', 'code', 'warehouse_id');

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $racks = $query->get()
            ->map(function ($rack) {
                return [
                    'value' => $rack->id,
                    'label' => $rack->full_name ?? "{$rack->code} - {$rack->name}",
                    'warehouse_id' => $rack->warehouse_id,
                ];
            });

        return response()->json($racks);
    }

    /**
     * Get products by type.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function byType($type)
    {
        $products = Product::where('type', $type)
            ->with(['warehouse', 'rack', 'category'])
            ->get();

        return ProductResource::collection($products);
    }
}
