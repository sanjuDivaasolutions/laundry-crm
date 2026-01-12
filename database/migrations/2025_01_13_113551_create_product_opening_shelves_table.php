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
 *  *  Last modified: 13/01/25, 5:06 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_opening_shelves', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity');
            $table->timestamps();

            $table->unsignedBigInteger('product_opening_id');
            $table->unsignedBigInteger('shelf_id');

            $table->foreign('product_opening_id')->references('id')->on('product_openings')->cascadeOnDelete();
            $table->foreign('shelf_id')->references('id')->on('shelves')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_opening_shelves');
    }
};
