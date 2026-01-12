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
 *  *  Last modified: 13/01/25, 9:28 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\FormRequests;

use App\Traits\CustomFormRequest;
use Illuminate\Support\Carbon;

class ProductInventoryRequest
{
    use CustomFormRequest;

    public static function prepareItems($i, $obj, $reason = 'opening'): array
    {
        $productId = $obj->id;
        $userId = adminAuth()->id();
        $date = Carbon::now()->format(config('project.date_format'));
        $inventories = [];

        $shelves = $i['shelves'] ?? [];
        $warehouseId = $i['warehouse_id'];

        foreach ($shelves as $shelf) {
            $quantity = (float) $shelf['quantity'];
            $amount = (float) $i['opening_stock_value'] / $quantity;

            $rate = 0;
            if ($quantity > 0 && $amount > 0) {
                $rate = $amount / $quantity;
            }

            $inventories[] = [
                'id' => null,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'shelf_id' => $shelf['shelf_id'],
                'batch_id' => null,
                'quantity' => $quantity,
                'rate' => $rate,
                'amount' => $amount,
                'reason' => $reason,
                'user_id' => $userId,
                'date' => $date,
            ];

        }

        return $inventories;

    }

    public static function prepareOpening($items, $obj)
    {
        foreach ($items as &$item) {
            if (isset($item['warehouse']) && $item['warehouse']) {
                $item['warehouse_id'] = $item['warehouse']['id'];
                unset($item['warehouse']);
            }
            $item['product_id'] = $obj->id;

            if (isset($item['shelves']) && $item['shelves']) {
                $item['shelves'] = self::prepareShelves($item['shelves'], $obj);
            } else {
                $item['shelves'] = [];
            }
        }

        return $items;
    }

    public static function prepareShelves($items, $obj)
    {
        $shelves = [];
        foreach ($items as $item) {
            $shelves[] = [
                'id' => $item['id'] ?? null,
                'product_opening_id' => $item['product_opening_id'],
                'shelf_id' => $item['shelf']['id'],
                'quantity' => $item['quantity'],
            ];
        }

        return $shelves;
    }
}
