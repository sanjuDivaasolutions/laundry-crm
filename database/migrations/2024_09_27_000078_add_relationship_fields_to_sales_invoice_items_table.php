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
 *  *  Last modified: 22/01/25, 4:11 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->foreign('sales_invoice_id', 'sales_invoice_fk_9572383')->references('id')->on('sales_invoices');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9572384')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9572387')->references('id')->on('units');
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->foreign('shelf_id', 'shelf_fk_6794612')->references('id')->on('shelves');
        });
    }

    public function down()
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign('sales_invoice_fk_9572383');
            $table->dropForeign('product_fk_9572384');
            $table->dropForeign('unit_fk_9572387');
            $table->dropForeign('shelf_fk_6794612');
            $table->dropColumn(['sales_invoice_id', 'product_id', 'unit_id', 'shelf_id']);
        });
    }
}
