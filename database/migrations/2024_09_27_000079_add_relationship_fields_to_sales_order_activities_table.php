<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSalesOrderActivitiesTable extends Migration
{
    public function up()
    {
        Schema::table('sales_order_activities', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_order_id')->nullable();
            $table->foreign('sale_order_id', 'sale_order_fk_9576135')->references('id')->on('sales_orders');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id', 'user_fk_9576138')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('sales_order_activities', function (Blueprint $table) {
            $table->dropForeign('sale_order_fk_9576135');
            $table->dropForeign('user_fk_9576138');
            $table->dropColumn(['sale_order_id', 'user_id']);
        });
    }
}
