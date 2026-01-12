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
 *  *  Last modified: 17/10/24, 3:10 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number')->unique();
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->longText('remark')->nullable();
            $table->string('type')->nullable();
            $table->string('reference_no')->nullable();
            $table->float('currency_rate', 11, 5)->nullable()->default(1);
            $table->float('sub_total', 15, 2)->nullable();
            $table->float('tax_total', 15, 2)->nullable();
            $table->float('tax_rate', 15, 2)->nullable();
            $table->float('grand_total', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_invoices');
    }
}
