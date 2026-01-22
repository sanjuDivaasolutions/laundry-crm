<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stripe webhook event log for idempotency and audit trail.
 *
 * @property int $id
 * @property string $stripe_event_id
 * @property string $event_type
 * @property int|null $tenant_id
 * @property string|null $stripe_customer_id
 * @property string|null $stripe_subscription_id
 * @property WebhookStatus $status
 * @property int $attempts
 * @property \Carbon\Carbon|null $processed_at
 * @property array|null $payload
 * @property array|null $processing_result
 * @property string|null $error_message
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StripeWebhookLog extends Model
{
    protected $fillable = [
        'stripe_event_id',
        'event_type',
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'status',
        'attempts',
        'processed_at',
        'payload',
        'processing_result',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'status' => WebhookStatus::class,
        'payload' => 'array',
        'processing_result' => 'array',
        'processed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the tenant associated with this webhook.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if this event has already been processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === WebhookStatus::PROCESSED;
    }

    /**
     * Check if this event is being processed.
     */
    public function isProcessing(): bool
    {
        return $this->status === WebhookStatus::PROCESSING;
    }

    /**
     * Mark the event as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => WebhookStatus::PROCESSING,
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Mark the event as successfully processed.
     */
    public function markAsProcessed(array $result = []): void
    {
        $this->update([
            'status' => WebhookStatus::PROCESSED,
            'processed_at' => now(),
            'processing_result' => $result,
            'error_message' => null,
        ]);
    }

    /**
     * Mark the event as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => WebhookStatus::FAILED,
            'error_message' => $error,
        ]);
    }

    /**
     * Mark the event as skipped (duplicate or irrelevant).
     */
    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'status' => WebhookStatus::SKIPPED,
            'processing_result' => ['skip_reason' => $reason],
        ]);
    }

    /**
     * Scope to find by Stripe event ID.
     */
    public function scopeByStripeEventId($query, string $eventId)
    {
        return $query->where('stripe_event_id', $eventId);
    }

    /**
     * Scope to find failed webhooks that can be retried.
     */
    public function scopeRetryable($query, int $maxAttempts = 3)
    {
        return $query->where('status', WebhookStatus::FAILED)
            ->where('attempts', '<', $maxAttempts);
    }

    /**
     * Scope for cleanup of old processed webhooks.
     */
    public function scopeOldProcessed($query, int $daysOld = 30)
    {
        return $query->where('status', WebhookStatus::PROCESSED)
            ->where('created_at', '<', now()->subDays($daysOld));
    }
}
