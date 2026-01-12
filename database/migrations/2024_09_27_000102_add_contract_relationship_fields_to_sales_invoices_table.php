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
 *  *  Last modified: 17/10/24, 5:48 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractRelationshipFieldsToSalesInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_revision_id')->nullable();
            $table->foreign('contract_revision_id', 'contract_revision_fk_6154748')->references('id')->on('contract_revisions');
        });
    }

    public function down()
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign('contract_revision_fk_6154748');
            $table->dropColumn('contract_revision_id');
        });
    }
}
