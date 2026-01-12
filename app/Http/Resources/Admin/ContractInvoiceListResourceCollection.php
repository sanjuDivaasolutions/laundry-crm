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
 *  *  Last modified: 17/10/24, 6:18 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ContractInvoiceListResourceCollection extends ResourceCollection
{
    protected mixed $contract = null;

    public function __construct($resource, $contract = null)
    {
        parent::__construct($resource);
        $this->contract = $contract;
    }

    public function toArray($request)
    {
        $data = ContractInvoiceListResource::collection($this->collection);

        return [
            'data' => $data,
            'columns' => $this->getColumns(),
            'summary' => $this->getSummary($data),
        ];
    }

    private function getColumns(): array
    {
        $columns = [
            [
                'title' => 'Invoice Number',
                'field' => 'invoice_number',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'Date',
                'field' => 'date',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
            [
                'title' => 'Sub Total',
                'field' => 'sub_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Tax Total',
                'field' => 'tax_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Grand Total',
                'field' => 'grand_total_text',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
                'align' => 'end',
            ],
            [
                'title' => 'Status',
                'field' => 'payment_status_label',
                'thComp' => 'TranslatedHeader',
                'sortable' => true,
            ],
        ];

        $revision = $this->contract->revision;
        if ($revision->contract_type === 'stripe') {
            /*$columns[] = [
                'title'    => 'Stripe Invoice',
                'field'    => 'stripe_invoice',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];
            $columns[] = [
                'title'    => 'Stripe Subscription',
                'field'    => 'stripe_subscription',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];
            $columns[] = [
                'title'    => 'Stripe Customer',
                'field'    => 'stripe_customer',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];
            $columns[] = [
                'title'    => 'Stripe Invoice URL',
                'field'    => 'stripe_invoice_url',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];
            $columns[] = [
                'title'    => 'Stripe PDF URL',
                'field'    => 'stripe_pdf_url',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];
            $columns[] = [
                'title'    => 'Stripe Meta',
                'field'    => 'stripe_meta',
                'thComp'   => 'TranslatedHeader',
                'sortable' => true,
            ];*/
        }
        if ($revision->contract_type['value'] == 'default') {
            $columns[] = [
                'title' => 'Actions',
                'field' => 'id',
                'thComp' => 'TranslatedHeader',
                'tdComp' => 'DatatableActions',
                'align' => 'center',
                'isActions' => true,
                'sortable' => false,
            ];
        }

        return $columns;
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
