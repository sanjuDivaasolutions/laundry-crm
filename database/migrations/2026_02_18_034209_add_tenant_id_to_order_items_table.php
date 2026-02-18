<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
        });

        // Backfill tenant_id from the parent order
        DB::statement('UPDATE order_items SET tenant_id = (SELECT tenant_id FROM orders WHERE orders.id = order_items.order_id) WHERE tenant_id IS NULL');

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'created_at'], 'order_items_tenant_created_idx');
        });

        // Fix barcode unique: drop global unique, add tenant-scoped
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['barcode']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unique(['tenant_id', 'barcode'], 'order_items_tenant_barcode_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique('order_items_tenant_barcode_unique');
            $table->unique('barcode');
            $table->dropIndex('order_items_tenant_created_idx');
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
