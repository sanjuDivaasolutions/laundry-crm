<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesInvoiceActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('sales_invoice_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_invoice_id')->nullable();
            $table->foreign('sale_invoice_id', 'sale_invoice_fk_9576202')->references('id')->on('sales_invoices');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9576203')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('sales_invoice_activities', function (Blueprint $table) {
            $table->dropForeign('sale_invoice_fk_9576202');
            $table->dropForeign('user_fk_9576203');
            $table->dropColumn(['sale_invoice_id', 'user_id']);
        });
    }
}
