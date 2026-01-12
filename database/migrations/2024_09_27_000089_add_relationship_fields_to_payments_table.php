<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->foreign('sales_order_id', 'sales_order_fk_10151150')->references('id')->on('sales_orders');
            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->foreign('sales_invoice_id', 'sales_invoice_fk_10148862')->references('id')->on('sales_invoices');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->foreign('purchase_order_id', 'purchase_order_fk_10151151')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->foreign('purchase_invoice_id', 'purchase_invoice_fk_10151152')->references('id')->on('purchase_orders');
            $table->unsignedBigInteger('payment_mode_id')->nullable();
            $table->foreign('payment_mode_id', 'payment_mode_fk_10148863')->references('id')->on('payment_modes');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_10151153')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('sales_order_fk_10151150');
            $table->dropForeign('sales_invoice_fk_10148862');
            $table->dropForeign('purchase_order_fk_10151151');
            $table->dropForeign('purchase_invoice_fk_10151152');
            $table->dropForeign('payment_mode_fk_10148863');
            $table->dropForeign('user_fk_10151153');
            $table->dropColumn(['sales_order_id', 'sales_invoice_id', 'purchase_order_id', 'purchase_invoice_id', 'payment_mode_id', 'user_id']);
        });
    }
}
