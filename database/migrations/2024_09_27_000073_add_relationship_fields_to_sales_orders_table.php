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
 *  *  Last modified: 16/10/24, 4:04 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_7894574')->references('id')->on('companies');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9570002')->references('id')->on('warehouses');
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->foreign('buyer_id', 'buyer_fk_9570006')->references('id')->on('buyers');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9570008')->references('id')->on('payment_terms');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9570014')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign('company_fk_7894574');
            $table->dropForeign('warehouse_fk_9570002');
            $table->dropForeign('buyer_fk_9570006');
            $table->dropForeign('payment_term_fk_9570008');
            $table->dropForeign('user_fk_9570014');
            $table->dropColumn(['company_id', 'warehouse_id', 'buyer_id', 'payment_term_id', 'user_id']);
        });
    }
}
