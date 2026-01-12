<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReceiveItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_receive_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->float('quantity', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_receive_items');
    }
}
