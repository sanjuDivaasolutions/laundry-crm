<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('description')->nullable();
            $table->string('sku')->nullable();
            $table->longText('remark')->nullable();
            $table->float('quantity', 15, 2)->nullable();
            $table->float('rate', 10, 2);
            $table->float('original_rate', 10, 2)->nullable();
            $table->float('amount', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_invoice_items');
    }
}
