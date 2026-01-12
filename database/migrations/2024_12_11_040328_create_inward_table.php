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
 *  *  Last modified: 11/12/24, 10:22 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inwards', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('reference_no')->nullable();
            $table->date('date')->nullable();
            $table->longText('remark')->nullable();
            $table->decimal('currency_rate', 11, 5)->nullable()->default(1);
            $table->decimal('sub_total', 15, 2)->nullable();
            $table->decimal('tax_total', 15, 2)->nullable();
            $table->decimal('tax_rate', 15, 2)->nullable();
            $table->decimal('grand_total', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('inwards', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_4587955')->references('id')->on('companies');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id', 'supplier_fk_9562444')->references('id')->on('suppliers');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9564438')->references('id')->on('warehouses');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9564440')->references('id')->on('users');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id', 'state_fk_9564441')->references('id')->on('states');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inwards');
    }
};
