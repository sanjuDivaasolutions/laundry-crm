<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates a key-value settings table for tenant-specific configurations.
     * Each setting has a type column for proper type casting on retrieval.
     *
     * Design Decision (from interview):
     * - Store with Type: Settings table has 'type' column (int, bool, string, json)
     * - This allows proper type casting when retrieving settings
     * - Avoids ambiguity like "credit_days" = "30" vs 30
     */
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'int', 'float', 'bool', 'json'])->default('string');
            $table->string('group', 50)->nullable()->comment('Group settings by category: general, branding, billing, notifications');
            $table->text('description')->nullable()->comment('Human-readable description for admin UI');
            $table->timestamps();

            // Unique constraint: one key per tenant
            $table->unique(['tenant_id', 'key']);

            // Index for group-based queries
            $table->index(['tenant_id', 'group']);
        });

        // Add some core settings columns to tenants table for frequently accessed values
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'timezone')) {
                $table->string('timezone', 50)->default('UTC')->after('settings');
            }
            if (!Schema::hasColumn('tenants', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('timezone');
            }
            if (!Schema::hasColumn('tenants', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('tenants', 'grace_period_ends_at')) {
                $table->timestamp('grace_period_ends_at')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('tenants', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('grace_period_ends_at');
            }
            if (!Schema::hasColumn('tenants', 'suspension_reason')) {
                $table->string('suspension_reason')->nullable()->after('suspended_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');

        Schema::table('tenants', function (Blueprint $table) {
            $columns = ['timezone', 'currency', 'logo_path', 'grace_period_ends_at', 'suspended_at', 'suspension_reason'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tenants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
