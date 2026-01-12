<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductInventoriesTable extends Migration
{
    public function up()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9532044')->references('id')->on('products');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9532046')->references('id')->on('warehouses');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->foreign('batch_id', 'batch_fk_9532047')->references('id')->on('product_batches');
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->foreign('shelf_id', 'shelf_fk_9532048')->references('id')->on('shelves');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9532054')->references('id')->on('users');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9556954')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('product_inventories', function (Blueprint $table) {
            $table->dropForeign('product_fk_9532044');
            $table->dropForeign('warehouse_fk_9532046');
            $table->dropForeign('batch_fk_9532047');
            $table->dropForeign('shelf_fk_9532048');
            $table->dropForeign('user_fk_9532054');
            $table->dropForeign('unit_fk_9556954');
            $table->dropColumn(['product_id', 'warehouse_id', 'batch_id', 'shelf_id', 'user_id', 'unit_id']);
        });
    }
}
