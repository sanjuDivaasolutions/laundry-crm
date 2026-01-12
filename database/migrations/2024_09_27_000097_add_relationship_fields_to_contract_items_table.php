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
 *  *  Last modified: 16/10/24, 6:08 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToContractItemsTable extends Migration
{
    public function up()
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id', 'contract_fk_9883095')->references('id')->on('contracts');
            $table->unsignedBigInteger('contract_revision_id')->nullable();
            $table->foreign('contract_revision_id', 'contract_revision_fk_9947255')->references('id')->on('contract_revisions');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9883096')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::table('contract_items', function (Blueprint $table) {
            $table->dropForeign('contract_fk_9883095');
            $table->dropForeign('contract_revision_fk_9947255');
            $table->dropForeign('product_fk_9883096');
            $table->dropColumn(['contract_id', 'contract_revision_id', 'product_id']);
        });
    }
}
