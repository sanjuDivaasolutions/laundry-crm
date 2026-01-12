<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('manufacturer_batch_no')->nullable();
            $table->date('manufacturer_date')->nullable();
            $table->boolean('active')->default(0)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_batches');
    }
}
