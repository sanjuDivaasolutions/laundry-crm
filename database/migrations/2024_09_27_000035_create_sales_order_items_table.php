<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku')->nullable();
            $table->longText('description')->nullable();
            $table->float('rate', 10, 2);
            $table->float('original_rate', 10, 2)->nullable();
            $table->float('quantity', 15, 2);
            $table->float('amount', 15, 2);
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_order_items');
    }
}
