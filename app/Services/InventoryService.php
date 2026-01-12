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
 *  *  Last modified: 06/02/25, 7:40 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\ProductInventory;
use App\Models\ProductStock;
use App\Models\ProductStockShelf;
use App\Models\SalesInvoice;
use App\Models\Shelf;
use App\Models\Warehouse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public static function updateProductStockInWarehouse($product_id, $warehouse_id): void
    {
        $warehouseData = ProductInventory::query()
            ->where('product_id', $product_id)
            ->where('warehouse_id', $warehouse_id)
            ->get();
        $shelfIds = $warehouseData->pluck('shelf_id')->toArray();

        $quantity = $warehouseData->sum('quantity');

        $shelves = Shelf::query()
            ->whereIn('id', $shelfIds)
            ->get();

        $stock = ProductStock::where('product_id', $product_id)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        if ($stock) {
            $stock->modified = Carbon::now()->format(config('project.date_format'));
            $stock->on_hand = $quantity;
            $stock->save();
        } else {
            $stock = new ProductStock();
            $stock->product_id = $product_id;
            $stock->warehouse_id = $warehouse_id;
            $stock->on_hand = $quantity;
            $stock->in_transit = 0;
            $stock->modified = Carbon::now()->format(config('project.date_format'));
            $stock->save();
        }

        $ignoreIds = [];
        foreach ($shelves as $shelf) {
            $shelfData = $warehouseData->where('shelf_id', $shelf->id);
            $quantity = $shelfData->sum('quantity');
            $stockShelf = $stock->shelves()->where('shelf_id', $shelf->id)->first();
            if ($stockShelf) {
                $stockShelf->on_hand = $quantity;
                $stockShelf->save();
            } else {
                $stockShelf = new ProductStockShelf();
                $stockShelf->product_stock_id = $stock->id;
                $stockShelf->shelf_id = $shelf->id;
                $stockShelf->on_hand = $quantity;
                $stockShelf->save();
            }
            $ignoreIds[] = $stockShelf->id;
        }
        ProductStockShelf::query()
            ->where('product_stock_id', $stock->id)
            ->whereNotIn('id', $ignoreIds)
            ->delete();
    }

    public static function updateProductStockAcrossAllWarehouses($product_id): void
    {
        $warehouses = Warehouse::all();
        foreach ($warehouses as $warehouse) {
            self::updateProductStockInWarehouse($product_id, $warehouse->id);
        }
    }

    public static function updateAllProductStocksInAllWarehouses(): void
    {
        $products = ProductInventory::select('product_id')->distinct()->get();
        foreach ($products as $product) {
            self::updateProductStockAcrossAllWarehouses($product->product_id);
        }
    }

    public static function updateAllProductStocksForWarehouse($warehouse_id): void
    {
        $products = ProductInventory::select('product_id')->distinct()->get();
        foreach ($products as $product) {
            self::updateProductStockInWarehouse($product->product_id, $warehouse_id);
        }
    }

    public static function updateStockBasedOnOrder($order, $reason, $shelfId = null): void
    {
        $warehouseId = $order->warehouse_id;
        if (!$shelfId) {
            $shelf = Shelf::query()
                ->where('warehouse_id', $warehouseId)
                ->where('is_default', true)
                ->first();
            $shelfId = $shelf?->id;
        }
        $user_id = getUserId();
        foreach ($order->items as $item) {

            $inventory = $item->inventory()->first();

            $itemShelfId = $item->shelf_id;
            if (!$itemShelfId) {
                $itemShelfId = $inventory?->shelf_id;
            }
            if (!$itemShelfId) {
                $itemShelfId = $shelfId;
            }

            $quantity = abs($item->quantity);
            if (in_array($reason, config('inventory.reasons.subtraction', []))) {
                $quantity = -1 * abs($item->quantity);
            }

            if (!$inventory) {
                $inventory = $item->inventory()->make();
                $inventory->product_id = $item->product_id;
                $inventory->rate = $item->rate;
                $inventory->unit_id = $item->unit_id;
                $inventory->reason = $reason;
            }

            $inventory->quantity = $quantity;
            $inventory->amount = $item->amount;
            $inventory->date = $order->date;
            $inventory->warehouse_id = $order->warehouse_id;
            $inventory->batch_id = null;
            $inventory->shelf_id = $itemShelfId;
            $inventory->user_id = $user_id;
            $inventory->save();

            self::updateProductStockInWarehouse($item->product_id, $order->warehouse_id);
        }
    }

    public static function updateAdjustmentInventoryStock($obj): void
    {
        $reason = $obj->reason;
        $isStockMovement = $reason === 'move';

        $obj->inventory()->delete();

        $inventory = null;

        $shelfId = $obj->shelf_id;

        if (!$shelfId) {
            return;
        }

        $shelf = Shelf::query()
            ->find($shelfId);

        if (!$shelf) {
            return;
        }

        $adjustedQuantity = $obj->adjusted_quantity;
        $warehouseId = $shelf?->warehouse_id;

        //Source Inventory
        if (!$inventory) {
            $inventory = $obj->inventory()->make();
        }
        $inventory->product_id = $obj->product_id;
        $inventory->rate = 0;
        $inventory->amount = 0;
        $inventory->quantity = $isStockMovement ? -1 * abs($adjustedQuantity) : $adjustedQuantity;
        $inventory->reason = $isStockMovement ? 'Source: ' . $obj->reason : $obj->reason;
        $inventory->date = $obj->date;
        $inventory->warehouse_id = $warehouseId;
        $inventory->shelf_id = $shelfId;

        $inventory->save();

        //Target Inventory
        $targetShelfId = $obj->target_shelf_id;
        if ($isStockMovement && $targetShelfId) {
            $targetShelf = Shelf::query()->find($targetShelfId);
            if ($targetShelf) {
                $targetWarehouseId = $targetShelf->warehouse_id;

                $targetInventory = $obj->inventory()->make();
                $targetInventory->product_id = $obj->product_id;
                $targetInventory->rate = 0;
                $targetInventory->amount = 0;
                $targetInventory->quantity = abs($adjustedQuantity);
                $targetInventory->reason = 'Target: ' . $obj->reason;
                $targetInventory->date = $obj->date;
                $targetInventory->warehouse_id = $targetWarehouseId;
                $targetInventory->shelf_id = $targetShelfId;
                $targetInventory->save();
            }

        }

        self::updateProductStockInWarehouse($obj->product_id, $warehouseId);
    }

    /**
     * Record inventory transactions for a POS sale.
     *
     * @param SalesInvoice $salesInvoice
     * @return void
     */
    public static function recordPosSale(SalesInvoice $salesInvoice): void
    {
        $reason = config('inventory.reasons.pos_sale', 'POS Sale');

        foreach ($salesInvoice->items as $item) {
            // Get product stocks ordered by FIFO
            $productStocks = ProductStock::where('product_id', $item->product_id)
                ->whereRaw('CAST(on_hand AS DECIMAL) > 0')
                ->orderBy('created_at')
                ->get();

            $remainingQty = $item->quantity;

            foreach ($productStocks as $stock) {
                if ($remainingQty <= 0) break;

                $currentStock = floatval($stock->on_hand ?? 0);
                $deductQty = min($remainingQty, $currentStock);

                // Create inventory transaction for this stock reduction
                ProductInventory::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'quantity' => -$deductQty,
                    'unit_cost' => $item->unit_price, // Or another cost basis if available
                    'reason' => $reason,
                    'inventoryable_type' => get_class($item),
                    'inventoryable_id' => $item->id,
                    'company_id' => $salesInvoice->company_id,
                    'user_id' => $salesInvoice->user_id,
                    'created_at' => now(),
                ]);

                // Update the stock on hand for the specific stock record
                $stock->on_hand = strval($currentStock - $deductQty);
                $stock->save();

                $remainingQty -= $deductQty;
            }

            if ($remainingQty > 0) {
                // This should be handled, maybe by throwing an exception
                // For now, we'll log it as an error
                \Log::error("Insufficient stock to complete POS sale for product ID: {$item->product_id}");
            }

            // After all deductions, update the consolidated stock record
            self::updateProductStockInWarehouse($item->product_id, $salesInvoice->warehouse_id);
        }
    }
}