<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\StripeWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to process Stripe webhooks asynchronously.
 *
 * Used for non-critical webhook events that don't need
 * immediate synchronous processing.
 */
class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $webhookLogId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $webhookLog = StripeWebhookLog::find($this->webhookLogId);

        if (!$webhookLog) {
            logger()->warning('Webhook log not found for async processing', [
                'webhook_log_id' => $this->webhookLogId,
            ]);
            return;
        }

        // Skip if already processed
        if ($webhookLog->isProcessed()) {
            logger()->debug('Webhook already processed, skipping async job', [
                'webhook_log_id' => $this->webhookLogId,
            ]);
            return;
        }

        try {
            $payload = $webhookLog->payload;
            $eventType = $webhookLog->event_type;

            // Process based on event type
            $result = $this->processEvent($eventType, $payload, $webhookLog);

            $webhookLog->markAsProcessed($result);

            logger()->info('Async webhook processing completed', [
                'webhook_log_id' => $this->webhookLogId,
                'event_type' => $eventType,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            $webhookLog->markAsFailed($e->getMessage());

            logger()->error('Async webhook processing failed', [
                'webhook_log_id' => $this->webhookLogId,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Process the webhook event.
     */
    protected function processEvent(string $eventType, array $payload, StripeWebhookLog $log): array
    {
        $method = 'handle' . str_replace(' ', '', ucwords(str_replace(['.', '_'], ' ', $eventType)));

        if (method_exists($this, $method)) {
            return $this->$method($payload, $log) ?? ['handled' => true];
        }

        return ['skipped' => true, 'reason' => 'no_handler'];
    }

    /**
     * Handle invoice created event.
     */
    protected function handleInvoiceCreated(array $payload, StripeWebhookLog $log): array
    {
        // Example: Send invoice notification to tenant admin
        $tenant = $log->tenant;

        if ($tenant) {
            // TODO: Dispatch notification
            // TenantInvoiceCreatedNotification::dispatch($tenant, $payload['data']['object']);
        }

        return ['notification_queued' => (bool) $tenant];
    }

    /**
     * Handle customer created event.
     */
    protected function handleCustomerCreated(array $payload, StripeWebhookLog $log): array
    {
        // Usually nothing to do - customer is created when tenant signs up
        return ['no_action_needed' => true];
    }

    /**
     * Handle customer updated event.
     */
    protected function handleCustomerUpdated(array $payload, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            return ['skipped' => true, 'reason' => 'tenant_not_found'];
        }

        // Sync payment method info if changed
        $customer = $payload['data']['object'] ?? [];

        if (isset($customer['invoice_settings']['default_payment_method'])) {
            // Payment method updated - could update local cache
        }

        return ['synced' => true];
    }

    /**
     * Handle payment method attached event.
     */
    protected function handlePaymentMethodAttached(array $payload, StripeWebhookLog $log): array
    {
        // Usually handled by Cashier automatically
        return ['delegated_to_cashier' => true];
    }

    /**
     * Handle payment method detached event.
     */
    protected function handlePaymentMethodDetached(array $payload, StripeWebhookLog $log): array
    {
        // Usually handled by Cashier automatically
        return ['delegated_to_cashier' => true];
    }

    /**
     * Handle the job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $webhookLog = StripeWebhookLog::find($this->webhookLogId);

        if ($webhookLog) {
            $webhookLog->markAsFailed('Job failed after all retries: ' . $exception->getMessage());
        }

        logger()->error('Stripe webhook job failed permanently', [
            'webhook_log_id' => $this->webhookLogId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying.
     */
    public function retryAfter(): int
    {
        return $this->backoff[$this->attempts() - 1] ?? 900;
    }
}
