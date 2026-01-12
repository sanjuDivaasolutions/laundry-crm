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
 *  *  Last modified: 11/12/24, 10:23 am
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inward_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('rate', 10, 2);
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('inward_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inward_id')->nullable();
            $table->foreign('inward_id', 'inward_fk_9564749')->references('id')->on('inwards');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9564750')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9563453')->references('id')->on('units');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inward_items');
    }
};
