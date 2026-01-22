<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates tables for SaaS plan management:
     * - plans: Main plan definitions with Stripe integration
     * - plan_features: Features included in each plan
     * - plan_quotas: Quota limits for each plan
     */
    public function up(): void
    {
        // Plans table
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // e.g., 'starter', 'professional', 'enterprise'
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('stripe_product_id', 100)->nullable()->index();
            $table->string('stripe_price_id', 100)->nullable()->index(); // Monthly price
            $table->string('stripe_yearly_price_id', 100)->nullable(); // Yearly price (optional)
            $table->integer('price_monthly')->default(0); // Price in cents
            $table->integer('price_yearly')->default(0); // Price in cents
            $table->string('currency', 3)->default('usd');
            $table->integer('trial_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Highlighted plan
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Additional plan data
            $table->timestamps();
        });

        // Plan Features - which features are enabled for each plan
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('feature_code', 50); // e.g., 'api_access', 'sso', 'priority_support'
            $table->boolean('enabled')->default(true);
            $table->json('config')->nullable(); // Feature-specific configuration
            $table->timestamps();

            $table->unique(['plan_id', 'feature_code']);
        });

        // Plan Quotas - quota limits for each plan
        Schema::create('plan_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('quota_code', 50); // e.g., 'max_users', 'storage_gb', 'api_calls'
            $table->integer('limit_value')->default(0); // -1 for unlimited
            $table->string('period', 20)->default('monthly'); // lifetime, daily, monthly, yearly
            $table->timestamps();

            $table->unique(['plan_id', 'quota_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_quotas');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }
};
