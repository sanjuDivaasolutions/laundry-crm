<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductPricesTable extends Migration
{
    public function up()
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9531922')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9531924')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('product_prices', function (Blueprint $table) {
            $table->dropForeign('product_fk_9531922');
            $table->dropForeign('unit_fk_9531924');
            $table->dropColumn(['product_id', 'unit_id']);
        });
    }
}
