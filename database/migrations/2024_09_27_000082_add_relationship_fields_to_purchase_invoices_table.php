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
 *  *  Last modified: 16/10/24, 4:05 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPurchaseInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_3325415')->references('id')->on('companies');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->foreign('purchase_order_id', 'purchase_order_fk_9875056')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id', 'supplier_fk_9874008')->references('id')->on('suppliers');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9874010')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign('company_fk_3325415');
            $table->dropForeign('purchase_order_fk_9875056');
            $table->dropForeign('supplier_fk_9874008');
            $table->dropForeign('user_fk_9874010');
            $table->dropColumn(['company_id', 'purchase_order_id', 'supplier_id', 'user_id']);
        });
    }
}
