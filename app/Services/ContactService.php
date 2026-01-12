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
 *  *  Last modified: 15/01/25, 2:23 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Services;

use App\Models\ContactAddress;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;

class ContactService
{
    public static function removeContact($obj): void
    {
        $billingAddressId = $obj->billing_address_id;
        $shippingAddressId = $obj->shipping_address_id;

        $obj->billing_address_id = null;
        $obj->shipping_address_id = null;
        $obj->save();

        $obj->delete();

        if ($billingAddressId) {
            ContactAddress::find($billingAddressId)->delete();
        }
        if ($shippingAddressId) {
            ContactAddress::find($shippingAddressId)->delete();
        }
    }

    public static function isBuyerUsed($obj): bool
    {
        $id = $obj->id;

        //Check in Sales Order & Sales Invoices
        $salesOrder = SalesOrder::query()->where('buyer_id', $id)->first();
        if ($salesOrder) {
            return true;
        }

        $salesInvoice = SalesInvoice::query()->where('buyer_id', $id)->first();
        if ($salesInvoice) {
            return true;
        }

        return false;
    }

    public static function isSupplierUsed($obj): bool
    {
        $id = $obj->id;

        //Check in Purchase Order & Purchase Invoices
        $purchaseOrder = PurchaseOrder::query()->where('supplier_id', $id)->first();
        if ($purchaseOrder) {
            return true;
        }

        $purchaseInvoice = PurchaseInvoice::query()->where('supplier_id', $id)->first();
        if ($purchaseInvoice) {
            return true;
        }

        return false;
    }
}
