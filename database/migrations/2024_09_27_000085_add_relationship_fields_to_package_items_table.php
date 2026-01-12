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
 *  *  Last modified: 23/01/25, 4:14 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPackageItemsTable extends Migration
{
    public function up()
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id', 'package_fk_9903707')->references('id')->on('packages');

            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9903708')->references('id')->on('products');

            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9903709')->references('id')->on('units');

            $table->unsignedBigInteger('sales_invoice_item_id');
            $table->foreign('sales_invoice_item_id', 'sales_invoice_item_fk_468713')->references('id')->on('sales_invoice_items');
        });
    }

    public function down()
    {
        Schema::table('package_items', function (Blueprint $table) {
            $table->dropForeign('package_fk_9903707');
            $table->dropForeign('product_fk_9903708');
            $table->dropForeign('unit_fk_9903709');
            $table->dropForeign('sales_invoice_item_fk_468713');
            $table->dropColumn(['package_id', 'product_id', 'unit_id', 'sales_invoice_item_id']);
        });
    }
}
