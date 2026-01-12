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
 *  *  Last modified: 11/02/25, 5:48 pm
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
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('tax_class_id')->constrained('tax_classes')->onDelete('cascade');
            //$table->foreignId('tax_rate_id')->constrained('tax_rates')->onDelete('cascade');
            $table->enum('based_on', ['shipping', 'payment', 'store'])->default('shipping');
            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();

            $table->unsignedBigInteger('tax_class_id');
            $table->foreign('tax_class_id')->references('id')->on('tax_classes')->onDelete('cascade');

            $table->unsignedBigInteger('tax_rate_id');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_rules');
    }
};
