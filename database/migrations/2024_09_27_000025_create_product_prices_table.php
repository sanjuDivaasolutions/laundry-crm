<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricesTable extends Migration
{
    public function up()
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('purchase_price', 9, 5);
            $table->float('sale_price', 9, 5);
            $table->float('lowest_sale_price', 9, 5);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_prices');
    }
}
