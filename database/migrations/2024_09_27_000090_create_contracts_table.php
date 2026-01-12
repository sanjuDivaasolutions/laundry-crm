<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->date('date');
            $table->longText('other_terms')->nullable();
            $table->longText('remark')->nullable();
            $table->integer('stripe_product')->nullable();
            $table->integer('stripe_product_price')->nullable();
            $table->longText('stripe_subscription_meta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
