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
 *  *  Last modified: 12/02/25, 4:37 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_6154748')->references('id')->on('companies');
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->foreign('sales_order_id', 'sales_order_fk_9895936')->references('id')->on('sales_orders');
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->foreign('buyer_id', 'buyer_fk_9572355')->references('id')->on('buyers');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9572357')->references('id')->on('users');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9896219')->references('id')->on('warehouses');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9896220')->references('id')->on('payment_terms');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id', 'state_fk_9896221')->references('id')->on('states');
        });
    }

    public function down()
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign('company_fk_6154748');
            $table->dropForeign('sales_order_fk_9895936');
            $table->dropForeign('buyer_fk_9572355');
            $table->dropForeign('user_fk_9572357');
            $table->dropForeign('warehouse_fk_9896219');
            $table->dropForeign('payment_term_fk_9896220');
            $table->dropForeign('state_fk_9896221');
            $table->dropColumn(['company_id', 'sales_order_id', 'buyer_id', 'user_id', 'warehouse_id', 'payment_term_id', 'state_id']);
        });
    }
}
