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
 *  *  Last modified: 12/12/24, 6:18 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InwardListResourceCollection extends ResourceCollection
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = InwardListResource::collection($this->collection);

        return [
            'data' => $data,
            'columns' => $this->getColumns(),
            'summary' => $this->getSummary($data),
        ];
    }

    private function getColumns(): array
    {
        return [
            [
                'title' => 'ID',
                'field' => 'id',
                'sortable' => true,
                'colStyle' => 'width: 100px;',
            ],
            [
                'title' => 'Date',
                'field' => 'date',
                'sortable' => true,
            ],
            [
                'title' => 'Invoice Number',
                'field' => 'invoice_number',
                'sortable' => true,
            ],
            [
                'title' => 'Company',
                'field' => 'company.name',
                'sortable' => true,
            ],
            [
                'title' => 'Warehouse',
                'field' => 'warehouse.name',
                'sortable' => true,
            ],
            [
                'title' => 'Supplier',
                'field' => 'supplier.display_name',
                'sortable' => true,
            ],
            [
                'title' => 'Sub Total',
                'field' => 'sub_total_label',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Tax Total',
                'field' => 'tax_total_label',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Grand Total',
                'field' => 'grand_total_label',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'User',
                'field' => 'user.name',
                'sortable' => true,
            ],
            [
                'title' => 'Actions',
                'field' => 'title',
                'tdComp' => 'DatatableActions',
                'isActions' => true,
                'sortable' => true,
            ],
        ];
    }

    private function getSummary($data): array
    {
        if ($data->isEmpty()) {
            return [];
        }

        $currencySign = '$';
        $subTotal = 0;
        $taxTotal = 0;
        $grandTotal = 0;
        foreach ($data as $item) {
            $subTotal += $item->sub_total;
            $taxTotal += $item->tax_total;
            $grandTotal += $item->grand_total;
        }

        return [
            'sub_total' => $subTotal,
            'sub_total_label' => $currencySign.number_format($subTotal, 2),
            'tax_total' => $taxTotal,
            'tax_total_label' => $currencySign.number_format($taxTotal, 2),
            'grand_total' => $grandTotal,
            'grand_total_label' => $currencySign.number_format($grandTotal, 2),
        ];
    }
}
