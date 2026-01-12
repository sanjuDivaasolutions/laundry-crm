<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('on_hand');
            $table->string('in_transit')->nullable();
            $table->date('modified');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}
