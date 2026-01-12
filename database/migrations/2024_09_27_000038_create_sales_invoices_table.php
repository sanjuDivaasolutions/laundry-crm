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
 *  *  Last modified: 12/02/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number');
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->longText('remark')->nullable();
            $table->string('type')->nullable();
            $table->string('order_type')->nullable();
            $table->string('reference_no')->nullable();
            $table->float('currency_rate', 11, 5)->nullable()->default(1);
            $table->float('sub_total', 10, 2);
            $table->float('tax_total', 10, 2);
            $table->float('tax_rate', 10, 2)->nullable();
            $table->float('grand_total', 10, 2);
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_invoices');
    }
}
