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
 *  *  Last modified: 21/01/25, 6:27 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->string('invoice_number');
            $table->longText('description');
            $table->decimal('tax_rate');
            $table->decimal('sub_total');
            $table->decimal('tax_total');
            $table->decimal('grand_total');
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();

            $table->unsignedBigInteger('expense_type_id');
            $table->foreign('expense_type_id')->references('id')->on('expense_types')->onDelete('cascade');

            $table->unsignedBigInteger('payment_mode_id');
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes')->onDelete('cascade');

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
