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
 *  *  Last modified: 12/02/25, 5:03 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_tax_details', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            //$table->foreignId('tax_rate_id')->constrained('tax_rates')->onDelete('cascade');
            $table->decimal('amount', 15, 4);
            $table->integer('priority')->default(1);
            $table->timestamps();

            $table->unsignedBigInteger('tax_rate_id');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');

            //Linking with sales_invoices table and inwards table using polymorphic relation
            $table->morphs('taxable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_tax_details');
    }
};
