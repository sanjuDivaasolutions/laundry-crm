<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToShelvesTable extends Migration
{
    public function up()
    {
        Schema::table('shelves', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9542624')->references('id')->on('warehouses');
        });
    }

    public function down()
    {
        Schema::table('shelves', function (Blueprint $table) {
            $table->dropForeign('warehouse_fk_9542624');
            $table->dropColumn('warehouse_id');
        });
    }
}
