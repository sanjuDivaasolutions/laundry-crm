<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToContractRevisionsTable extends Migration
{
    public function up()
    {
        Schema::table('contract_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->foreign('contract_id', 'contract_fk_9917857')->references('id')->on('contracts');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9917859')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('contract_revisions', function (Blueprint $table) {
            $table->dropForeign('contract_fk_9917857');
            $table->dropForeign('user_fk_9917859');
            $table->dropColumn(['contract_id', 'user_id']);
        });
    }
}
