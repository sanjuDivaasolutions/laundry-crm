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

class AddRelationshipFieldsToPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id', 'company_fk_4587945')->references('id')->on('companies');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->foreign('supplier_id', 'supplier_fk_9562434')->references('id')->on('suppliers');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9562436')->references('id')->on('payment_terms');
            $table->unsignedBigInteger('shipment_mode_id')->nullable();
            $table->foreign('shipment_mode_id', 'shipment_mode_fk_9562437')->references('id')->on('shipment_modes');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9562438')->references('id')->on('warehouses');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9562440')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign('company_fk_4587945');
            $table->dropForeign('supplier_fk_9562434');
            $table->dropForeign('payment_term_fk_9562436');
            $table->dropForeign('shipment_mode_fk_9562437');
            $table->dropForeign('warehouse_fk_9562438');
            $table->dropForeign('user_fk_9562440');
            $table->dropColumn(['company_id', 'supplier_id', 'payment_term_id', 'shipment_mode_id', 'warehouse_id', 'user_id']);
        });
    }
}
