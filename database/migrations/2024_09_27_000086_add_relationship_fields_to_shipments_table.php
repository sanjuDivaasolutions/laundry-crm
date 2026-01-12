<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToShipmentsTable extends Migration
{
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id', 'package_fk_9904002')->references('id')->on('packages');
            $table->unsignedBigInteger('shipment_mode_id')->nullable();
            $table->foreign('shipment_mode_id', 'shipment_mode_fk_9915268')->references('id')->on('shipment_modes');
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign('package_fk_9904002');
            $table->dropForeign('shipment_mode_fk_9915268');
            $table->dropColumn(['package_id', 'shipment_mode_id']);
        });
    }
}
