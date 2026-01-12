<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'hsn_code')) {
                $table->string('hsn_code')->nullable()->after('is_returnable');
                $table->index('hsn_code');
            }

            if (!Schema::hasColumn('products', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('hsn_code');
                $table->index('batch_number');
            }

            if (!Schema::hasColumn('products', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('batch_number')->constrained()->onDelete('set null');
                $table->index('warehouse_id');
            }

            if (!Schema::hasColumn('products', 'rack_id')) {
                $table->foreignId('rack_id')->nullable()->after('warehouse_id')->constrained()->onDelete('set null');
                $table->index('rack_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['rack_id']);
            $table->dropIndex(['hsn_code']);
            $table->dropIndex(['batch_number']);
            $table->dropIndex(['warehouse_id']);
            $table->dropIndex(['rack_id']);
            $table->dropColumn(['hsn_code', 'batch_number', 'warehouse_id', 'rack_id']);
        });
    }
};