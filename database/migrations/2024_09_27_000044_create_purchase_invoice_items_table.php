<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku')->nullable();
            $table->longText('description')->nullable();
            $table->float('rate', 15, 2)->nullable();
            $table->float('quantity', 15, 2)->nullable();
            $table->float('amount', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_invoice_items');
    }
}
