<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToProductFeaturesTable extends Migration
{
    public function up()
    {
        Schema::table('product_features', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id', 'product_fk_9531893')->references('id')->on('products');
            $table->unsignedBigInteger('feature_id')->nullable();
            $table->foreign('feature_id', 'feature_fk_9531894')->references('id')->on('features');
        });
    }

    public function down()
    {
        Schema::table('product_features', function (Blueprint $table) {
            $table->dropForeign('product_fk_9531893');
            $table->dropForeign('feature_fk_9531894');
            $table->dropColumn(['product_id', 'feature_id']);
        });
    }
}
