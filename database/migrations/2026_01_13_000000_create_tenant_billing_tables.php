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
        // Tenant Subscriptions
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., 'default', 'premium'
            $table->string('stripe_id')->unique();
            $table->string('stripe_status');
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'stripe_status']);
        });

        // Tenant Subscription Items
        Schema::create('tenant_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->onDelete('cascade');
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->integer('quantity')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'stripe_price']);
        });

        // Tenant Invoices (Local Cache / Records)
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('stripe_id')->unique();
            $table->integer('total'); // Amount in cents
            $table->string('currency')->default('usd');
            $table->string('status'); // paid, open, void, uncollectible
            $table->string('hosted_invoice_url')->nullable();
            $table->string('invoice_pdf')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->timestamps();
        });
        
        // Add Stripe Customer ID to tenants table if not exists
        if (!Schema::hasColumn('tenants', 'stripe_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('stripe_id')->nullable()->index();
                $table->string('pm_type')->nullable();
                $table->string('pm_last_four', 4)->nullable();
                $table->timestamp('trial_ends_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
        Schema::dropIfExists('tenant_subscription_items');
        Schema::dropIfExists('tenant_subscriptions');
        
        if (Schema::hasColumn('tenants', 'stripe_id')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropColumn([
                    'stripe_id',
                    'pm_type',
                    'pm_last_four',
                    'trial_ends_at',
                ]);
            });
        }
    }
};
