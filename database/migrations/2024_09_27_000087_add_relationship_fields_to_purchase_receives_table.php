<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPurchaseReceivesTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_receives', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9918839')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('purchase_receives', function (Blueprint $table) {
            $table->dropForeign('user_fk_9918839');
            $table->dropColumn('user_id');
        });
    }
}
