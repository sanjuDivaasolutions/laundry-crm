<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToEstimatesTable extends Migration
{
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->foreign('warehouse_id', 'warehouse_fk_9572194')->references('id')->on('warehouses');
            $table->unsignedBigInteger('buyer_id')->nullable();
            $table->foreign('buyer_id', 'buyer_fk_9572196')->references('id')->on('buyers');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9572199')->references('id')->on('payment_terms');
        });
    }

    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign('warehouse_fk_9572194');
            $table->dropForeign('buyer_fk_9572196');
            $table->dropForeign('payment_term_fk_9572199');
            $table->dropColumn(['warehouse_id', 'buyer_id', 'payment_term_id']);
        });
    }
}
