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
 *  *  Last modified: 07/01/25, 4:18 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_9531846')->references('id')->on('companies');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id', 'category_fk_9531846')->references('id')->on('categories');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id', 'supplier_fk_9531852')->references('id')->on('suppliers');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9531854')->references('id')->on('users');
            $table->unsignedBigInteger('unit_01_id')->nullable();
            $table->foreign('unit_01_id', 'unit_01_fk_9556952')->references('id')->on('units');
            $table->unsignedBigInteger('unit_02_id')->nullable();
            $table->foreign('unit_02_id', 'unit_02_fk_9556953')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('company_fk_9531846');
            $table->dropForeign('category_fk_9531846');
            $table->dropForeign('supplier_fk_9531852');
            $table->dropForeign('user_fk_9531854');
            $table->dropForeign('unit_01_fk_9556952');
            $table->dropForeign('unit_02_fk_9556953');
            $table->dropColumn(['company_id', 'category_id', 'supplier_id', 'user_id', 'unit_01_id', 'unit_02_id']);
        });
    }
}
