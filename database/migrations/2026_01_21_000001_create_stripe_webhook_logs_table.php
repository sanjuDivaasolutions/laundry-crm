<?php

declare(strict_types=1);

use App\Enums\WebhookStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates table for Stripe webhook event logging and idempotency.
     */
    public function up(): void
    {
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();

            // Stripe event identification (used for idempotency)
            $table->string('stripe_event_id', 255)->unique();
            $table->string('event_type', 100)->index();

            // Related entities
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->string('stripe_customer_id', 255)->nullable()->index();
            $table->string('stripe_subscription_id', 255)->nullable()->index();

            // Processing status
            $table->string('status', 20)->default(WebhookStatus::PENDING->value)->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('processed_at')->nullable();

            // Payload and response storage
            $table->json('payload')->nullable();
            $table->json('processing_result')->nullable();
            $table->text('error_message')->nullable();

            // Audit trail
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            // Composite index for cleanup queries
            $table->index(['status', 'created_at']);
            $table->index(['event_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
