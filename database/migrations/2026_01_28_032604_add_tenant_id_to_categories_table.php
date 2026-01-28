<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds tenant_id to the categories table to support
     * multi-tenant category isolation.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Add tenant_id column if not exists
            if (!Schema::hasColumn('categories', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            }
        });

        // Drop the unique constraint on name (categories can have same name across tenants)
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        // Add composite unique constraint (tenant_id + name)
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['tenant_id', 'name'], 'categories_tenant_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Drop composite unique
            $table->dropUnique('categories_tenant_name_unique');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Restore simple unique on name
            $table->unique('name');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
