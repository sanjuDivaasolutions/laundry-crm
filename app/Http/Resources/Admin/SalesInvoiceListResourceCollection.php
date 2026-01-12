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
 *  *  Last modified: 22/01/25, 10:28 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SalesInvoiceListResourceCollection extends ResourceCollection
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = SalesInvoiceListResource::collection($this->collection);

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
                'title' => 'general.fields.po_number',
                'field' => 'reference_no',
                'sortable' => true,
            ],
            [
                'title' => 'Buyer',
                'field' => 'buyer.display_name',
                'sortable' => true,
            ],
            [
                'title' => 'Sub Total',
                'field' => 'sub_total_text',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Commission',
                'field' => 'commission_total_text',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Tax Total',
                'field' => 'tax_total_text',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Grand Total',
                'field' => 'grand_total_text',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Payment Status',
                'field' => 'payment_status',
                'tdComp' => 'PaymentStatusLink',
                'downloadField' => 'payment_status_label',
                'sortable' => false,
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
        $currencySign = '$';
        $subTotal = 0;
        $commissionTotal = 0;
        $taxTotal = 0;
        $grandTotal = 0;
        foreach ($data as $item) {
            $subTotal += $item->sub_total;
            $commissionTotal += $item->commission_total;
            $taxTotal += $item->tax_total;
            $grandTotal += $item->grand_total;
        }

        return [
            'sub_total' => $subTotal,
            'sub_total_text' => $currencySign.number_format($subTotal, 2),
            'commission_total' => $commissionTotal,
            'commission_total_text' => $currencySign.number_format($commissionTotal, 2),
            'tax_total' => $taxTotal,
            'tax_total_text' => $currencySign.number_format($taxTotal, 2),
            'grand_total' => $grandTotal,
            'grand_total_text' => $currencySign.number_format($grandTotal, 2),
        ];
    }
}
