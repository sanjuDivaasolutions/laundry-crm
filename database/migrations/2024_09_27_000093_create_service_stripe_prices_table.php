<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceStripePricesTable extends Migration
{
    public function up()
    {
        Schema::create('service_stripe_prices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stripe_price');
            $table->float('price', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_stripe_prices');
    }
}
