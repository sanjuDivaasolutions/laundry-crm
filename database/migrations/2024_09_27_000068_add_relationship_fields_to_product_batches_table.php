<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductBatchesTable extends Migration
{
    public function up()
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->unsignedBigInteger('shelf_id')->nullable();
            $table->foreign('shelf_id', 'shelf_fk_9531971')->references('id')->on('shelves');
        });
    }

    public function down()
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropForeign('shelf_fk_9531971');
            $table->dropColumn('shelf_id');
        });
    }
}
