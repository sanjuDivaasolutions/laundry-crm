<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tables that require tenant_id to be non-nullable.
     */
    private array $tables = ['users', 'roles', 'companies'];

    /**
     * Run the migrations.
     *
     * Makes tenant_id non-nullable for strict tenant isolation.
     * This is safe for fresh installations. For existing data,
     * run data migration first.
     */
    public function up(): void
    {
        // Check for orphaned records (records without tenant_id)
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $orphanCount = DB::table($table)->whereNull('tenant_id')->count();

            if ($orphanCount > 0) {
                throw new \RuntimeException(
                    "Cannot make tenant_id non-nullable: {$orphanCount} orphaned records found in {$table}. " .
                    "Run data migration first or delete orphaned records."
                );
            }
        }

        // Make tenant_id non-nullable
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                // Drop existing foreign key constraint before modifying the column
                $table->dropForeign(['tenant_id']);
                
                // Make tenant_id non-nullable
                $table->foreignId('tenant_id')->nullable(false)->change();
                
                // Re-add the foreign key constraint
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) {
                // Drop the foreign key constraint
                $table->dropForeign(['tenant_id']);

                // Revert tenant_id to nullable
                $table->foreignId('tenant_id')->nullable()->change();

                // Re-add the foreign key constraint
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            });
        }
    }
};
