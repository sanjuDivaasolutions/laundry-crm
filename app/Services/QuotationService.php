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
 *  *  Last modified: 21/01/25, 5:39 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\QuotationStatus;

class QuotationService
{
    public static function getCode($company, $table, $field = 'order_no', $separator = '/', $digits = 4): string
    {
        $prefix = 'QUO' . $separator . $company->code . $separator . date('y') . $separator;
        $length = strlen($prefix) + $digits;

        $where = [
            [$field, 'LIKE', $prefix . '%'],
        ];

        $config = [
            'table'  => $table,
            'field'  => $field,
            'length' => $length,
            'prefix' => $prefix,
            'where'  => $where,
        ];
        return UtilityService::generateCode($config);

    }

    public static function getItemsObject($request): array
    {
        $unsets = ['product', 'unit'];
        $items = stringToArray($request->input('items', []));
        foreach ($items as &$i) {
            if (!isset($i['product']['id']) || !$i['product']['id']) {
                continue;
            }
            $i['product_id'] = $i['product']['id'];
            $i['title'] = $i['product']['name'];
            $i['sku'] = $i['product']['sku'];
            if (isset($i['unit']) && $i['unit']) {
                $i['unit_id'] = $i['unit']['id'];
            }
            foreach ($unsets as $unset) {
                unset($i[$unset]);
            }
        }
        return $items;
    }

    public static function updateTotals($obj): void
    {
        $subTotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        $items = $obj->items()->get();
        foreach ($items as $item) {
            $subTotal += $item->amount;
        }

        $mixTotal = $subTotal;

        /*$discounts = InvoiceDiscount::query()->where('invoice_id',$obj->id)->get();
        foreach ($discounts as $d) {
            $discountTotal += ($mixTotal * $d['rate']) / 100;
        }*/

        $mixTotal = $subTotal - $discountTotal;

        /*$taxes = InvoiceTax::query()->where('invoice_id',$obj->id)->get();
        foreach ($taxes as $t) {
            $taxTotal += ($mixTotal * $t['rate']) / 100;
        }*/

        //Calculate 5% tax on total amount
        $taxTotal = ($mixTotal * 5) / 100;

        $mixTotal = $mixTotal + $taxTotal;

        $obj->sub_total = $subTotal;
        /* $obj->discount_total = $discountTotal; */
        $obj->tax_total = $taxTotal;
        $obj->grand_total = $mixTotal;
        $obj->save();
    }

    public static function setStatus($obj, $status, $date = null, $remark = null): void
    {
        $s = new QuotationStatus();
        $s->quotation_id = $obj->id;
        $s->status = $status;
        $s->date = $date;
        $s->remark = $remark;
        $s->user_id = auth()->id();
        $s->active = 1;
        $s->save();

        QuotationStatus::query()
            ->where('quotation_id', $obj->id)
            ->where('id', '!=', $s->id)
            ->update(['active' => 0]);
    }
}
