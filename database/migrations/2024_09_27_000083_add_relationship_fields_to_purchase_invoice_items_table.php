<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPurchaseInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9874285')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9874288')->references('id')->on('units');
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->foreign('purchase_invoice_id', 'purchase_invoice_fk_9877653')->references('id')->on('purchase_invoices');
        });
    }

    public function down()
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropForeign('product_fk_9874285');
            $table->dropForeign('unit_fk_9874288');
            $table->dropForeign('purchase_invoice_fk_9877653');
            $table->dropColumn(['product_id', 'unit_id', 'purchase_invoice_id']);
        });
    }
}
