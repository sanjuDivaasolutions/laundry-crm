<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // service_prices: individual indexes for JOIN lookups
        try {
            Schema::table('service_prices', function (Blueprint $table) {
                $table->index('service_id', 'sp_service_id_idx');
                $table->index('item_id', 'sp_item_id_idx');
            });
        } catch (\Exception) {
            // Indexes already exist
        }

        // order_items: tenant-scoped item and service indexes
        try {
            Schema::table('order_items', function (Blueprint $table) {
                $table->index(['tenant_id', 'item_id'], 'oi_tenant_item_idx');
                $table->index(['tenant_id', 'service_id'], 'oi_tenant_service_idx');
            });
        } catch (\Exception) {
            // Indexes already exist
        }

        // payments: tenant and customer history indexes
        try {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('tenant_id', 'p_tenant_idx');
                $table->index(['customer_id', 'payment_date'], 'p_customer_date_idx');
            });
        } catch (\Exception) {
            // Indexes already exist
        }

        // orders: POS Kanban board composite index
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->index(['tenant_id', 'processing_status_id', 'order_date'], 'o_tenant_status_date_idx');
            });
        } catch (\Exception) {
            // Indexes already exist
        }
    }

    public function down(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropIndex('sp_service_id_idx');
            $table->dropIndex('sp_item_id_idx');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('oi_tenant_item_idx');
            $table->dropIndex('oi_tenant_service_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('p_tenant_idx');
            $table->dropIndex('p_customer_date_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('o_tenant_status_date_idx');
        });
    }
};
