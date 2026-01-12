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
 *  *  Last modified: 15/01/25, 2:35 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Warehouse;

class ProductService
{
    public static function fixOpeningStockAcrossAllProducts(): void
    {
        $products = Product::query()->onlyProducts()->get();
        $products->each(function ($product) {
            self::fixOpeningStock($product);
        });
    }

    public static function fixOpeningStock(Product $product): void
    {
        if (!$product->has_inventory) return;

        $warehouses = Warehouse::all();
        $opening = $product->opening()->get();

        foreach ($warehouses as $warehouse) {
            $openingStock = $opening->where('warehouse_id', $warehouse->id)->first();

            if ($opening->where('warehouse_id', $warehouse->id)->count() > 1) {
                $opening->where('warehouse_id', $warehouse->id)->each->delete();
                $openingStock = null;
            }

            if (!$openingStock) {
                $product->opening()->create([
                    'warehouse_id'        => $warehouse->id,
                    'opening_stock'       => 0,
                    'opening_stock_value' => 0,
                ]);
            }
        }

    }

    public static function fixPrices(Product $product)
    {
        $product->load([
            'unit_01',
            'unit_02',
            'prices',
        ]);

        // Check if price exists for both units if both units are different
        $units = [$product->unit_01_id];
        if ($product->unit_01_id !== $product->unit_02_id) {
            $units[] = $product->unit_02_id;
        }

        $prices = ProductPrice::query()
            ->where('product_id', $product->id)
            ->whereIn('unit_id', $units)
            ->get();

        foreach ($units as $unit) {
            $price = $prices->where('unit_id', $unit)->first();
            if (!$price) {
                ProductPrice::create([
                    'product_id'        => $product->id,
                    'unit_id'           => $unit,
                    'purchase_price'    => 0,
                    'sale_price'        => 0,
                    'lowest_sale_price' => 0,
                ]);
            }
        }

    }

    public static function remove(Product $product): void
    {
        //check if product is assigned to inwards, contract, or service
        abort_if($product->inwardItem()->exists(), 400, 'Product is assigned to inwards');

        abort_if($product->contractItem()->exists(), 400, 'Product is assigned to contracts');

        abort_if($product->salesInvoiceItem()->exists(), 400, 'Product is assigned to sales invoices');

        $product->features()->delete();
        $product->prices()->delete();
        $product->opening()->delete();
        $product->stock()->delete();
        $product->delete();
    }
}
