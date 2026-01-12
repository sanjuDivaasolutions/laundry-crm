<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToContactAddressesTable extends Migration
{
    public function up()
    {
        Schema::table('contact_addresses', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id', 'country_fk_9530970')->references('id')->on('countries');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->foreign('state_id', 'state_fk_9530971')->references('id')->on('states');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->foreign('city_id', 'city_fk_9530972')->references('id')->on('cities');
        });
    }

    public function down()
    {
        Schema::table('contact_addresses', function (Blueprint $table) {
            $table->dropForeign('country_fk_9530970');
            $table->dropForeign('state_fk_9530971');
            $table->dropForeign('city_fk_9530972');
            $table->dropColumn(['country_id', 'state_id', 'city_id']);
        });
    }
}
