<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Enums\WebhookStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhook;
use App\Models\StripeWebhookLog;
use App\Models\Tenant;
use App\Notifications\PaymentFailedNotification;
use App\Notifications\SubscriptionEndingNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

/**
 * Custom Stripe webhook controller with idempotency and audit logging.
 *
 * Note: This controller does NOT extend Laravel Cashier's WebhookController
 * to avoid method signature conflicts. We handle all webhook processing
 * ourselves with proper idempotency and audit logging.
 *
 * Key Security Features:
 * - Signature verification using Stripe webhook secret
 * - Idempotency check using stripe_event_id
 * - Full audit trail in database
 * - Async processing for heavy operations
 * - Proper error handling and retry support
 */
class StripeWebhookController extends Controller
{
    /**
     * Events that require synchronous processing (critical billing state changes).
     */
    private const SYNC_EVENTS = [
        'customer.subscription.created',
        'customer.subscription.updated',
        'customer.subscription.deleted',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
    ];

    /**
     * Events that can be processed asynchronously.
     */
    private const ASYNC_EVENTS = [
        'invoice.created',
        'invoice.finalized',
        'customer.created',
        'customer.updated',
        'payment_method.attached',
        'payment_method.detached',
    ];

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // 1. Verify webhook signature
        try {
            $event = $this->verifyWebhookSignature($payload, $signature);
        } catch (SignatureVerificationException $e) {
            logger()->warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return $this->errorResponse('Invalid signature', 400);
        }

        // 2. Check idempotency - has this event been processed?
        $webhookLog = $this->getOrCreateWebhookLog($event, $request);

        if ($webhookLog->isProcessed()) {
            logger()->debug('Stripe webhook already processed (idempotent skip)', [
                'event_id' => $event->id,
                'event_type' => $event->type,
            ]);
            return $this->successResponse('Already processed');
        }

        if ($webhookLog->isProcessing()) {
            // Another process is handling this - return success to prevent retry
            logger()->debug('Stripe webhook currently being processed', [
                'event_id' => $event->id,
            ]);
            return $this->successResponse('Processing in progress');
        }

        // 3. Process the webhook
        try {
            $webhookLog->markAsProcessing();

            if ($this->shouldProcessAsync($event->type)) {
                // Queue for async processing
                ProcessStripeWebhook::dispatch($webhookLog->id);
                $webhookLog->update(['processing_result' => ['queued' => true]]);

                logger()->info('Stripe webhook queued for async processing', [
                    'event_id' => $event->id,
                    'event_type' => $event->type,
                ]);
            } else {
                // Process synchronously for critical events
                $result = $this->processWebhookEvent($event, $webhookLog);
                $webhookLog->markAsProcessed($result);

                logger()->info('Stripe webhook processed synchronously', [
                    'event_id' => $event->id,
                    'event_type' => $event->type,
                    'result' => $result,
                ]);
            }

            return $this->successResponse('Webhook processed');
        } catch (\Exception $e) {
            $webhookLog->markAsFailed($e->getMessage());

            logger()->error('Stripe webhook processing failed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 so Stripe retries
            return $this->errorResponse('Processing failed', 500);
        }
    }

    /**
     * Verify the Stripe webhook signature.
     */
    protected function verifyWebhookSignature(string $payload, ?string $signature): Event
    {
        $secret = config('cashier.webhook.secret');

        if (!$secret) {
            throw new \RuntimeException('Stripe webhook secret not configured');
        }

        return Webhook::constructEvent(
            $payload,
            $signature ?? '',
            $secret,
            config('cashier.webhook.tolerance', 300)
        );
    }

    /**
     * Get existing webhook log or create new one (idempotency check).
     */
    protected function getOrCreateWebhookLog(Event $event, Request $request): StripeWebhookLog
    {
        return DB::transaction(function () use ($event, $request) {
            // Use lockForUpdate to prevent race conditions
            $existing = StripeWebhookLog::where('stripe_event_id', $event->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            // Extract tenant from Stripe customer
            $tenant = $this->resolveTenantFromEvent($event);

            return StripeWebhookLog::create([
                'stripe_event_id' => $event->id,
                'event_type' => $event->type,
                'tenant_id' => $tenant?->id,
                'stripe_customer_id' => $this->extractCustomerId($event),
                'stripe_subscription_id' => $this->extractSubscriptionId($event),
                'status' => WebhookStatus::PENDING,
                'payload' => $event->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });
    }

    /**
     * Resolve tenant from Stripe event data.
     */
    protected function resolveTenantFromEvent(Event $event): ?Tenant
    {
        $customerId = $this->extractCustomerId($event);

        if (!$customerId) {
            return null;
        }

        return Tenant::where('stripe_id', $customerId)->first();
    }

    /**
     * Extract Stripe customer ID from event.
     */
    protected function extractCustomerId(Event $event): ?string
    {
        $data = $event->data->object;

        // Direct customer field
        if (isset($data->customer) && is_string($data->customer)) {
            return $data->customer;
        }

        // Customer object
        if (isset($data->id) && str_starts_with($data->id, 'cus_')) {
            return $data->id;
        }

        return null;
    }

    /**
     * Extract Stripe subscription ID from event.
     */
    protected function extractSubscriptionId(Event $event): ?string
    {
        $data = $event->data->object;

        // Direct subscription field
        if (isset($data->subscription) && is_string($data->subscription)) {
            return $data->subscription;
        }

        // Subscription object
        if (isset($data->id) && str_starts_with($data->id, 'sub_')) {
            return $data->id;
        }

        return null;
    }

    /**
     * Determine if event should be processed asynchronously.
     */
    protected function shouldProcessAsync(string $eventType): bool
    {
        // Critical events always process synchronously
        if (in_array($eventType, self::SYNC_EVENTS)) {
            return false;
        }

        // Known async events
        if (in_array($eventType, self::ASYNC_EVENTS)) {
            return true;
        }

        // Default: process unknown events synchronously to be safe
        return false;
    }

    /**
     * Process the webhook event.
     */
    protected function processWebhookEvent(Event $event, StripeWebhookLog $log): array
    {
        $method = $this->getHandlerMethod($event->type);

        if (method_exists($this, $method)) {
            return $this->$method($event->data->object, $log) ?? ['handled' => true];
        }

        // No specific handler - log and skip
        return $this->handleUnknownEvent($event);
    }

    /**
     * Get the handler method name for an event type.
     */
    protected function getHandlerMethod(string $eventType): string
    {
        return 'handle' . str_replace(' ', '', ucwords(str_replace(['.', '_'], ' ', $eventType)));
    }

    /**
     * Handle events we don't explicitly handle.
     */
    protected function handleUnknownEvent(Event $event): array
    {
        logger()->debug('Unhandled Stripe webhook event', [
            'event_id' => $event->id,
            'event_type' => $event->type,
        ]);

        return ['skipped' => true, 'reason' => 'no_handler_defined'];
    }

    /**
     * Handle subscription created event.
     */
    protected function handleCustomerSubscriptionCreated($subscription, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            logger()->warning('Subscription created for unknown tenant', [
                'stripe_customer_id' => $subscription->customer,
            ]);
            return ['warning' => 'tenant_not_found'];
        }

        // Reset quotas for new subscription period
        $this->resetTenantQuotas($tenant);

        logger()->info('Tenant subscription created', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        return [
            'tenant_id' => $tenant->id,
            'subscription_status' => $subscription->status,
            'quotas_reset' => true,
        ];
    }

    /**
     * Handle subscription updated event.
     */
    protected function handleCustomerSubscriptionUpdated($subscription, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            return ['warning' => 'tenant_not_found'];
        }

        // Check for billing period renewal (quota reset trigger)
        if ($this->isNewBillingPeriod($subscription, $tenant)) {
            $this->resetTenantQuotas($tenant);
        }

        logger()->info('Tenant subscription updated', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
        ]);

        return [
            'tenant_id' => $tenant->id,
            'subscription_status' => $subscription->status,
        ];
    }

    /**
     * Handle subscription deleted/canceled event.
     */
    protected function handleCustomerSubscriptionDeleted($subscription, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            return ['warning' => 'tenant_not_found'];
        }

        logger()->info('Tenant subscription canceled', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
        ]);

        // Send cancellation notification
        $owner = $tenant->users()->first();
        if ($owner) {
            $endsAt = isset($subscription->current_period_end)
                ? date('F j, Y', $subscription->current_period_end)
                : 'soon';

            $owner->notify(new SubscriptionEndingNotification(
                endsAt: $endsAt,
                isCanceled: true
            ));
        }

        return [
            'tenant_id' => $tenant->id,
            'subscription_canceled' => true,
            'notification_sent' => $owner !== null,
        ];
    }

    /**
     * Handle successful payment event.
     */
    protected function handleInvoicePaymentSucceeded($invoice, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            return ['warning' => 'tenant_not_found'];
        }

        // Check if this is a subscription renewal
        if ($invoice->subscription && $invoice->billing_reason === 'subscription_cycle') {
            $this->resetTenantQuotas($tenant);
        }

        logger()->info('Tenant payment succeeded', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_paid,
        ]);

        return [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'amount_paid' => $invoice->amount_paid,
        ];
    }

    /**
     * Handle failed payment event.
     */
    protected function handleInvoicePaymentFailed($invoice, StripeWebhookLog $log): array
    {
        $tenant = $log->tenant;

        if (!$tenant) {
            return ['warning' => 'tenant_not_found'];
        }

        logger()->warning('Tenant payment failed', [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'attempt_count' => $invoice->attempt_count,
        ]);

        // Send dunning notification to tenant owner
        $owner = $tenant->users()->first();
        if ($owner) {
            $owner->notify(new PaymentFailedNotification(
                invoiceId: $invoice->id,
                amountDue: $invoice->amount_due ?? 0,
                attemptCount: $invoice->attempt_count ?? 1,
                failureReason: $invoice->last_payment_error?->message ?? null
            ));
        }

        return [
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'payment_failed' => true,
            'notification_sent' => $owner !== null,
        ];
    }

    /**
     * Check if subscription billing period has renewed.
     */
    protected function isNewBillingPeriod($subscription, Tenant $tenant): bool
    {
        // Compare current_period_start with what we have stored
        // This is a simplified check - enhance based on your needs
        $localSubscription = $tenant->subscriptions()
            ->where('stripe_id', $subscription->id)
            ->first();

        if (!$localSubscription) {
            return false;
        }

        // If the period start changed, it's a new billing period
        return $subscription->current_period_start > optional($localSubscription->updated_at)->timestamp;
    }

    /**
     * Reset tenant quotas for new billing period.
     */
    protected function resetTenantQuotas(Tenant $tenant): void
    {
        // Reset all periodic quotas (not lifetime)
        $tenant->usages()
            ->whereHas('quota', function ($query) {
                $query->where('period', '!=', 'lifetime');
            })
            ->update([
                'current_usage' => 0,
                'reset_at' => now(),
            ]);

        logger()->info('Tenant quotas reset', ['tenant_id' => $tenant->id]);
    }

    /**
     * Return success response.
     */
    protected function successResponse(string $message = 'OK'): JsonResponse
    {
        return response()->json(['message' => $message], 200);
    }

    /**
     * Return error response.
     */
    protected function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }
}
