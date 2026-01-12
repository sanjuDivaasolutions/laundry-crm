<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductStocksTable extends Migration
{
    public function up()
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9550636')->references('id')->on('products');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9550637')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign('product_fk_9550636');
            $table->dropForeign('warehouse_fk_9550637');
            $table->dropColumn(['product_id', 'warehouse_id']);
        });
    }
}
