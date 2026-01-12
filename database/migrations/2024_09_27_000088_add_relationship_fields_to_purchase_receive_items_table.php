<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPurchaseReceiveItemsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_receive_items', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_receive_id')->nullable();
            $table->foreign('purchase_receive_id', 'purchase_receive_fk_9918880')->references('id')->on('purchase_receives');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->foreign('purchase_order_id', 'purchase_order_fk_9918881')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->foreign('purchase_invoice_id', 'purchase_invoice_fk_9918882')->references('id')->on('purchase_invoices');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9918883')->references('id')->on('products');
        });
    }

    public function down()
    {
        Schema::table('purchase_receive_items', function (Blueprint $table) {
            $table->dropForeign('purchase_receive_fk_9918880');
            $table->dropForeign('purchase_order_fk_9918881');
            $table->dropForeign('purchase_invoice_fk_9918882');
            $table->dropForeign('product_fk_9918883');
            $table->dropColumn(['purchase_receive_id', 'purchase_order_id', 'purchase_invoice_id', 'product_id']);
        });
    }
}
