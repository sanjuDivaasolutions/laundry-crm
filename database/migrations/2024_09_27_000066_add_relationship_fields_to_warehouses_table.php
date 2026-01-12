<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToWarehousesTable extends Migration
{
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id', 'city_fk_9531939')->references('id')->on('cities');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id', 'state_fk_9531940')->references('id')->on('states');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id', 'country_fk_9531941')->references('id')->on('countries');
        });
    }

    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign('city_fk_9531939');
            $table->dropForeign('state_fk_9531940');
            $table->dropForeign('country_fk_9531941');
            $table->dropColumn(['city_id', 'state_id', 'country_id']);
        });
    }
}
