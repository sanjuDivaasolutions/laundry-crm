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
 *  *  Last modified: 12/12/24, 6:37 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace Database\Seeders\Project;

use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class SalesInvoiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $count = 100;
        for ($i = 0; $i < $count; $i++) {
            $obj = SalesInvoice::factory()->create();

            $item = SalesInvoiceItem::factory()->create();
            $item->sales_invoice_id = $obj->id;
            $item->save();

            OrderService::updateSalesOrderTotals($obj);
            InventoryService::updateStockBasedOnOrder($obj, 'sale');
        }
    }
}
