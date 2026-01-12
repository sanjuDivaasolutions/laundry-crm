<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPurchaseOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->foreign('purchase_order_id', 'purchase_order_fk_9563749')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9563750')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9563753')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign('purchase_order_fk_9563749');
            $table->dropForeign('product_fk_9563750');
            $table->dropForeign('unit_fk_9563753');
            $table->dropColumn(['purchase_order_id', 'product_id', 'unit_id']);
        });
    }
}
