<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToTranslationsTable extends Migration
{
    public function up()
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->nullable();
            $table->foreign('language_id', 'language_fk_9530606')->references('id')->on('languages');
            $table->unsignedBigInteger('language_term_id')->nullable();
            $table->foreign('language_term_id', 'language_term_fk_9530607')->references('id')->on('language_terms');
        });
    }

    public function down()
    {
        Schema::table('translations', function (Blueprint $table) {
            $table->dropForeign('language_fk_9530606');
            $table->dropForeign('language_term_fk_9530607');
            $table->dropColumn(['language_id', 'language_term_id']);
        });
    }
}
