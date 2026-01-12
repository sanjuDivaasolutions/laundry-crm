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
 *  *  Last modified: 17/10/24, 3:11 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_number');
            $table->date('date')->nullable();
            $table->date('estimated_shipment_date')->nullable();
            $table->longText('remarks')->nullable();
            $table->float('freight_total', 10, 2)->nullable();
            $table->string('discount_type')->nullable();
            $table->float('currency_rate', 11, 5)->nullable()->default(1);
            $table->float('discount_total', 10, 2)->nullable();
            $table->float('discount_rate', 10, 2)->nullable();
            $table->float('sub_total', 10, 2);
            $table->float('tax_rate', 10, 2);
            $table->float('tax_total', 10, 2);
            $table->float('grand_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
