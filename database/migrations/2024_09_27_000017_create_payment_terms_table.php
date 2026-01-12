<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTermsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('days');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_terms');
    }
}
