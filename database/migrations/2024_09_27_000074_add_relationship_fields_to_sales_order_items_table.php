<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->foreign('sales_order_id', 'sales_order_fk_9888910')->references('id')->on('sales_orders');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9572151')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9572152')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropForeign('sales_order_fk_9888910');
            $table->dropForeign('product_fk_9572151');
            $table->dropForeign('unit_fk_9572152');
            $table->dropColumn(['sales_order_id', 'product_id', 'unit_id']);
        });
    }
}
