<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToEstimateActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('estimate_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('estimate_id')->nullable();
            $table->foreign('estimate_id', 'estimate_fk_9576379')->references('id')->on('estimates');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9576382')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('estimate_activities', function (Blueprint $table) {
            $table->dropForeign('estimate_fk_9576379');
            $table->dropForeign('user_fk_9576382');
            $table->dropColumn(['estimate_id', 'user_id']);
        });
    }
}
