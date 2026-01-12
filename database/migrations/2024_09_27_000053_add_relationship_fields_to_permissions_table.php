<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToPermissionsTable extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_group_id')->nullable();
            $table->foreign('permission_group_id', 'permission_group_fk_9530580')->references('id')->on('permission_groups');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign('permission_group_fk_9530580');
            $table->dropColumn('permission_group_id');
        });
    }
}
