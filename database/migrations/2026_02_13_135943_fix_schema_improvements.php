<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix order_number: change from global unique to tenant-scoped unique
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->unique(['tenant_id', 'order_number'], 'orders_tenant_order_number_unique');
        });

        // Fix payment_number: change from global unique to tenant-scoped unique
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['payment_number']);
            $table->unique(['tenant_id', 'payment_number'], 'payments_tenant_payment_number_unique');
        });

        // Add soft deletes to order_items for data integrity
        Schema::table('order_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add missing indexes for common queries
        Schema::table('orders', function (Blueprint $table) {
            $table->index('urgent', 'orders_urgent_idx');
            $table->index('payment_status', 'orders_payment_status_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('is_active', 'customers_is_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_tenant_order_number_unique');
            $table->unique('order_number');
            $table->dropIndex('orders_urgent_idx');
            $table->dropIndex('orders_payment_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_tenant_payment_number_unique');
            $table->unique('payment_number');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_is_active_idx');
        });
    }
};
