<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToStatesTable extends Migration
{
    public function up()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id', 'country_fk_9530621')->references('id')->on('countries');
        });
    }

    public function down()
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropForeign('country_fk_9530621');
            $table->dropColumn('country_id');
        });
    }
}
