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
 *  *  Last modified: 09/01/25, 6:52 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inward_item_shelves', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantity');
            $table->timestamps();

            $table->unsignedBigInteger('inward_item_id')->nullable();
            $table->foreign('inward_item_id', 'inward_item_fk_497498')->references('id')->on('inward_items');

            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->foreign('shelf_id', 'shelf_fk_9784611')->references('id')->on('shelves');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inward_item_shelves');
    }
};
