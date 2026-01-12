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
 *  *  Last modified: 23/01/25, 4:16 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPackagesTable extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->foreign('sales_invoice_id', 'sales_invoice_fk_9905780')->references('id')->on('sales_invoices');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9903719')->references('id')->on('users');
            $table->unsignedBigInteger('packing_type_id')->nullable();
            $table->foreign('packing_type_id', 'packing_type_fk_9904024')->references('id')->on('packing_types');
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign('sales_invoice_fk_9905780');
            $table->dropForeign('user_fk_9903719');
            $table->dropForeign('packing_type_fk_9904024');
            $table->dropColumn(['sales_invoice_id', 'user_id', 'packing_type_id']);
        });
    }
}
