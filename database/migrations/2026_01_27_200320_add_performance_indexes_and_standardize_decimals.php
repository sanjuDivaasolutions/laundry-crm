<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['item_id', 'service_id'], 'order_items_item_service_index');
            $table->decimal('unit_price', 10, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['tenant_id', 'order_number'], 'orders_tenant_number_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_tenant_number_index');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('order_items_item_service_index');
            $table->decimal('unit_price', 8, 2)->change();
        });
    }
};
