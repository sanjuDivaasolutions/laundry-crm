<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReceivesTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_receives', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->date('date');
            $table->longText('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_receives');
    }
}
