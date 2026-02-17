<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables and their composite indexes for tenant queries.
     *
     * Design Decision (from interview):
     * - Composite Indexes: Add (tenant_id, created_at), (tenant_id, is_active) etc.
     * - These significantly improve query performance for tenant-scoped queries
     *
     * Format: 'table' => [
     *     'index_name' => ['column1', 'column2', ...],
     * ]
     */
    protected array $indexes = [
        'items' => [
            'items_tenant_active_idx' => ['tenant_id', 'is_active'],
            'items_tenant_created_idx' => ['tenant_id', 'created_at'],
        ],
        'orders' => [
            'orders_tenant_status_idx' => ['tenant_id', 'status'],
            'orders_tenant_created_idx' => ['tenant_id', 'created_at'],
            'orders_tenant_customer_idx' => ['tenant_id', 'customer_id'],
        ],
        'order_items' => [
            'order_items_tenant_item_idx' => ['tenant_id', 'item_id'],
            'order_items_tenant_order_idx' => ['tenant_id', 'order_id'],
        ],
        'customers' => [
            'customers_tenant_created_idx' => ['tenant_id', 'created_at'],
            'customers_tenant_phone_idx' => ['tenant_id', 'phone'],
        ],
        'payments' => [
            'payments_tenant_created_idx' => ['tenant_id', 'created_at'],
            'payments_tenant_order_idx' => ['tenant_id', 'order_id'],
        ],
        'users' => [
            'users_tenant_email_idx' => ['tenant_id', 'email'],
            'users_tenant_created_idx' => ['tenant_id', 'created_at'],
        ],
        'companies' => [
            'companies_tenant_active_idx' => ['tenant_id', 'active'],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->indexes as $table => $tableIndexes) {
            // Skip if table doesn't exist
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $tableIndexes) {
                foreach ($tableIndexes as $indexName => $columns) {
                    // Check if all columns exist
                    $allColumnsExist = true;
                    foreach ($columns as $column) {
                        if (! Schema::hasColumn($table, $column)) {
                            $allColumnsExist = false;
                            break;
                        }
                    }

                    if (! $allColumnsExist) {
                        continue;
                    }

                    // Check if index already exists
                    if ($this->indexExists($table, $indexName)) {
                        continue;
                    }

                    $blueprint->index($columns, $indexName);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->indexes as $table => $tableIndexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $tableIndexes) {
                foreach ($tableIndexes as $indexName => $columns) {
                    if ($this->indexExists($table, $indexName)) {
                        $blueprint->dropIndex($indexName);
                    }
                }
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if ($driver === 'mysql') {
            $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

            return count($result) > 0;
        }

        if ($driver === 'pgsql') {
            $result = DB::select(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $indexName]
            );

            return count($result) > 0;
        }

        if ($driver === 'sqlite') {
            $result = DB::select(
                "SELECT 1 FROM sqlite_master WHERE type = 'index' AND name = ?",
                [$indexName]
            );

            return count($result) > 0;
        }

        return false;
    }
};
