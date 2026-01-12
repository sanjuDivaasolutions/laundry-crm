<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToLanguageTermsTable extends Migration
{
    public function up()
    {
        Schema::table('language_terms', function (Blueprint $table) {
            $table->unsignedBigInteger('language_term_group_id')->nullable();
            $table->foreign('language_term_group_id', 'language_term_group_fk_9530604')->references('id')->on('language_term_groups');
        });
    }

    public function down()
    {
        Schema::table('language_terms', function (Blueprint $table) {
            $table->dropForeign('language_term_group_fk_9530604');
            $table->dropColumn('language_term_group_id');
        });
    }
}
