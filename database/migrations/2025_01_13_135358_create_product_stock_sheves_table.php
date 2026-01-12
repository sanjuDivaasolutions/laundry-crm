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
 *  *  Last modified: 13/01/25, 7:24 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_stock_shelves', function (Blueprint $table) {
            $table->id();
            $table->decimal('on_hand');
            $table->decimal('in_transit');
            $table->timestamps();

            $table->unsignedBigInteger('product_stock_id');
            $table->foreign('product_stock_id', 'product_stock_fk_4499999')->references('id')->on('product_stocks');

            $table->unsignedBigInteger('shelf_id');
            $table->foreign('shelf_id', 'shelf_fk_4500000')->references('id')->on('shelves');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stock_shelves');
    }
};
