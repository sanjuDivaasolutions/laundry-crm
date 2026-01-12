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
 *  *  Last modified: 12/02/25, 4:21 pm
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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('geo_zone_id')->constrained('geo_zones')->onDelete('cascade');
            //$table->foreignId('state_id')->constrained('states')->onDelete('cascade'); // Added state_id
            $table->string('name', 255);
            $table->decimal('rate', 15, 4);
            $table->integer('priority')->default(1);
            $table->enum('type', ['P', 'F'])->default('P'); // 'P' for Percentage, 'F' for Fixed
            $table->timestamps();

            $table->unsignedBigInteger('geo_zone_id');
            $table->foreign('geo_zone_id')->references('id')->on('geo_zones')->onDelete('cascade');

            $table->unsignedBigInteger('state_id');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_rates');
    }
};
