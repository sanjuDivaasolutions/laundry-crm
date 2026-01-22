<?php

declare(strict_types=1);

use App\Enums\WebhookStatus;
use App\Models\StripeWebhookLog;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create([
        'name' => 'Test Tenant',
        'stripe_id' => 'cus_test123',
        'active' => true,
    ]);

    // Mock webhook secret
    config(['cashier.webhook.secret' => 'whsec_test_secret']);
});

describe('SEC-002: Stripe Webhook Idempotency', function () {

    it('creates webhook log entry for new events', function () {
        $eventId = 'evt_' . uniqid();

        StripeWebhookLog::create([
            'stripe_event_id' => $eventId,
            'event_type' => 'customer.subscription.created',
            'tenant_id' => $this->tenant->id,
            'stripe_customer_id' => $this->tenant->stripe_id,
            'status' => WebhookStatus::PENDING,
            'payload' => ['test' => 'data'],
        ]);

        $this->assertDatabaseHas('stripe_webhook_logs', [
            'stripe_event_id' => $eventId,
            'event_type' => 'customer.subscription.created',
        ]);
    });

    it('prevents duplicate event processing via unique constraint', function () {
        $eventId = 'evt_duplicate_test';

        // First insertion should succeed
        StripeWebhookLog::create([
            'stripe_event_id' => $eventId,
            'event_type' => 'invoice.paid',
            'status' => WebhookStatus::PROCESSED,
        ]);

        // Second insertion with same event ID should fail
        expect(fn() => StripeWebhookLog::create([
            'stripe_event_id' => $eventId,
            'event_type' => 'invoice.paid',
            'status' => WebhookStatus::PENDING,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('marks webhook as processed after successful handling', function () {
        $log = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_process_test',
            'event_type' => 'customer.subscription.updated',
            'tenant_id' => $this->tenant->id,
            'status' => WebhookStatus::PENDING,
        ]);

        $log->markAsProcessing();
        expect($log->fresh()->status)->toBe(WebhookStatus::PROCESSING);

        $log->markAsProcessed(['result' => 'success']);
        expect($log->fresh()->status)->toBe(WebhookStatus::PROCESSED);
        expect($log->fresh()->processed_at)->not->toBeNull();
    });

    it('marks webhook as failed with error message', function () {
        $log = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_fail_test',
            'event_type' => 'invoice.payment_failed',
            'status' => WebhookStatus::PROCESSING,
        ]);

        $log->markAsFailed('Payment processing error');

        $refreshed = $log->fresh();
        expect($refreshed->status)->toBe(WebhookStatus::FAILED);
        expect($refreshed->error_message)->toBe('Payment processing error');
    });

    it('increments attempt count on each processing attempt', function () {
        $log = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_retry_test',
            'event_type' => 'customer.updated',
            'status' => WebhookStatus::PENDING,
            'attempts' => 0,
        ]);

        $log->markAsProcessing();
        expect($log->fresh()->attempts)->toBe(1);

        // Simulate failure and retry
        $log->markAsFailed('Temporary error');
        $log->markAsProcessing();
        expect($log->fresh()->attempts)->toBe(2);
    });

    it('identifies retryable failed webhooks', function () {
        // Create various webhook states
        StripeWebhookLog::create([
            'stripe_event_id' => 'evt_failed_1',
            'event_type' => 'test',
            'status' => WebhookStatus::FAILED,
            'attempts' => 1,
        ]);

        StripeWebhookLog::create([
            'stripe_event_id' => 'evt_failed_max',
            'event_type' => 'test',
            'status' => WebhookStatus::FAILED,
            'attempts' => 5, // Exceeded max
        ]);

        StripeWebhookLog::create([
            'stripe_event_id' => 'evt_processed',
            'event_type' => 'test',
            'status' => WebhookStatus::PROCESSED,
            'attempts' => 1,
        ]);

        $retryable = StripeWebhookLog::retryable(3)->get();

        expect($retryable)->toHaveCount(1);
        expect($retryable->first()->stripe_event_id)->toBe('evt_failed_1');
    });

    it('finds old processed webhooks for cleanup', function () {
        // Create old processed webhook
        $old = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_old',
            'event_type' => 'test',
            'status' => WebhookStatus::PROCESSED,
            'created_at' => now()->subDays(45),
        ]);

        // Create recent processed webhook
        StripeWebhookLog::create([
            'stripe_event_id' => 'evt_recent',
            'event_type' => 'test',
            'status' => WebhookStatus::PROCESSED,
            'created_at' => now()->subDays(5),
        ]);

        $oldLogs = StripeWebhookLog::oldProcessed(30)->get();

        expect($oldLogs)->toHaveCount(1);
        expect($oldLogs->first()->stripe_event_id)->toBe('evt_old');
    });

});

describe('Webhook Status Enum', function () {

    it('correctly identifies terminal states', function () {
        expect(WebhookStatus::PROCESSED->isTerminal())->toBeTrue();
        expect(WebhookStatus::FAILED->isTerminal())->toBeTrue();
        expect(WebhookStatus::SKIPPED->isTerminal())->toBeTrue();
        expect(WebhookStatus::PENDING->isTerminal())->toBeFalse();
        expect(WebhookStatus::PROCESSING->isTerminal())->toBeFalse();
    });

    it('correctly identifies retryable states', function () {
        expect(WebhookStatus::FAILED->canRetry())->toBeTrue();
        expect(WebhookStatus::PROCESSED->canRetry())->toBeFalse();
        expect(WebhookStatus::PENDING->canRetry())->toBeFalse();
    });

});

describe('Webhook Event Association', function () {

    it('associates webhook with correct tenant via stripe_customer_id', function () {
        $log = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_assoc_test',
            'event_type' => 'invoice.paid',
            'tenant_id' => $this->tenant->id,
            'stripe_customer_id' => $this->tenant->stripe_id,
            'status' => WebhookStatus::PROCESSED,
        ]);

        expect($log->tenant->id)->toBe($this->tenant->id);
        expect($log->stripe_customer_id)->toBe($this->tenant->stripe_id);
    });

    it('stores full webhook payload for debugging', function () {
        $payload = [
            'id' => 'evt_payload_test',
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_test',
                    'customer' => 'cus_test',
                    'status' => 'active',
                ],
            ],
        ];

        $log = StripeWebhookLog::create([
            'stripe_event_id' => 'evt_payload_test',
            'event_type' => 'customer.subscription.created',
            'status' => WebhookStatus::PROCESSED,
            'payload' => $payload,
        ]);

        expect($log->payload)->toBe($payload);
        expect($log->payload['data']['object']['status'])->toBe('active');
    });

});
