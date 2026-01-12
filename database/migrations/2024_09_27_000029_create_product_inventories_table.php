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
 *  *  Last modified: 10/12/24, 6:10 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductInventoriesTable extends Migration
{
    public function up()
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('inventoryable_type')->nullable(); // Polymorphic relationship type
            $table->unsignedBigInteger('inventoryable_id')->nullable(); // Polymorphic relationship ID
            $table->string('reason')->nullable();
            $table->date('date');
            $table->float('rate', 9, 5);
            $table->integer('quantity')->nullable();
            $table->float('amount', 9, 5);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_inventories');
    }
}
