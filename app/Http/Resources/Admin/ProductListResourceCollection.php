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
 *  *  Last modified: 12/12/24, 7:07 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

/** @see \App\Models\Product */
class ProductListResourceCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $data = ProductListResource::collection($this->collection);

        return [
            'data'    => $data,
            'summary' => $this->getSummary($data),
            'columns' => $this->getColumns(),
        ];
    }

    private function getColumns(): array
    {
        return [
            [
                'title'    => 'ID',
                'field'    => 'id',
                'sortable' => true,
                'colStyle' => 'width: 100px;',
            ],
            [
                'title'    => 'Code',
                'field'    => 'code',
                'sortable' => true,
            ],
            [
                'title'    => 'Name',
                'field'    => 'name',
                'sortable' => true,
            ],
            [
                'title'    => 'SKU',
                'field'    => 'sku',
                'sortable' => true,
            ],
            [
                'title'    => 'Category',
                'field'    => 'category.name',
                'sortable' => true,
            ],
            [
                'title'    => 'In-Transit',
                'field'    => 'in_transit_label',
                'sortable' => false,
                'align'    => 'end',
            ],
            [
                'title'    => 'On-Hand',
                'field'    => 'on_hand_label',
                'sortable' => false,
                'align'    => 'end',
            ],
            [
                'title'     => 'Actions',
                'field'     => 'title',
                'thComp'    => 'TranslatedHeader',
                'tdComp'    => 'DatatableActions',
                'isActions' => true,
            ],
        ];
    }

    private function getSummary($data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        $onHand = 0;
        $inTransit = 0;

        foreach ($data as &$item) {
            $stock = $item->stock;

            $itemOnHand = $stock->sum('on_hand');
            $itemInTransit = $stock->sum('in_transit');

            $item->on_hand_label = $itemOnHand;
            $item->in_transit_label = $itemInTransit;

            $onHand += $itemOnHand;
            $inTransit += $itemInTransit;
        }

        return [
            'on_hand_label'    => $onHand,
            'in_transit_label' => $inTransit,
        ];
    }
}
