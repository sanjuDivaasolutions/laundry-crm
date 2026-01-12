<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('sales_order_activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->boolean('is_active')->default(0);
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_order_activities');
    }
}
