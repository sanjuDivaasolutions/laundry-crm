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
 *  *  Last modified: 21/01/25, 4:54 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuotationListResourceCollection extends ResourceCollection
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = QuotationListResource::collection($this->collection);

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
                'title' => 'general.fields.id',
                'field' => 'id',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'colStyle' => 'width: 100px;',
            ],
            [
                'title' => 'general.fields.date',
                'field' => 'date',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'general.fields.order_no',
                'field' => 'order_no',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'general.fields.buyer',
                'field' => 'buyer.display_name',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'general.fields.subTotal',
                'field' => 'sub_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'general.fields.taxTotal',
                'field' => 'tax_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'general.fields.grandTotal',
                'field' => 'grand_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'general.fields.user',
                'field' => 'user.name',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'Actions',
                'field' => 'title',
                'thComp' => 'TranslatedHeader',
                'tdComp' => 'DatatableActions',
                'isActions' => true,
                'sortable' => true,
            ],
        ];
    }

    private function getSummary($data): array
    {
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
            'sub_total_text' => $currencySign.number_format($subTotal, 2),
            'tax_total' => $taxTotal,
            'tax_total_text' => $currencySign.number_format($taxTotal, 2),
            'grand_total' => $grandTotal,
            'grand_total_text' => $currencySign.number_format($grandTotal, 2),
        ];
    }
}
