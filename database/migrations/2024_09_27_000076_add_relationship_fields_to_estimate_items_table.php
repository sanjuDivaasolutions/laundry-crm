<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToEstimateItemsTable extends Migration
{
    public function up()
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->unsignedBigInteger('estimate_id')->nullable();
            $table->foreign('estimate_id', 'estimate_fk_9572230')->references('id')->on('estimates');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9572231')->references('id')->on('products');
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('unit_id', 'unit_fk_9572233')->references('id')->on('units');
        });
    }

    public function down()
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropForeign('estimate_fk_9572230');
            $table->dropForeign('product_fk_9572231');
            $table->dropForeign('unit_fk_9572233');
            $table->dropColumn(['estimate_id', 'product_id', 'unit_id']);
        });
    }
}
