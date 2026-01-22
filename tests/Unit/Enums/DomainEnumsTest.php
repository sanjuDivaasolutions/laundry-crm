<?php

declare(strict_types=1);

use App\Enums\QuotaPeriod;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantStatus;
use App\Enums\WebhookStatus;

describe('ARCH-001: Domain Enums', function () {

    describe('SubscriptionStatus Enum', function () {

        it('has all expected Stripe statuses', function () {
            $expectedStatuses = [
                'active', 'past_due', 'unpaid', 'canceled',
                'incomplete', 'incomplete_expired', 'trialing', 'paused',
            ];

            foreach ($expectedStatuses as $status) {
                expect(SubscriptionStatus::tryFrom($status))->not->toBeNull();
            }
        });

        it('correctly identifies access-allowing statuses', function () {
            expect(SubscriptionStatus::ACTIVE->allowsAccess())->toBeTrue();
            expect(SubscriptionStatus::TRIALING->allowsAccess())->toBeTrue();
            expect(SubscriptionStatus::PAST_DUE->allowsAccess())->toBeTrue(); // Grace period

            expect(SubscriptionStatus::CANCELED->allowsAccess())->toBeFalse();
            expect(SubscriptionStatus::UNPAID->allowsAccess())->toBeFalse();
            expect(SubscriptionStatus::INCOMPLETE_EXPIRED->allowsAccess())->toBeFalse();
        });

        it('correctly identifies grace period status', function () {
            expect(SubscriptionStatus::PAST_DUE->isGracePeriod())->toBeTrue();
            expect(SubscriptionStatus::ACTIVE->isGracePeriod())->toBeFalse();
        });

        it('correctly identifies statuses needing payment action', function () {
            expect(SubscriptionStatus::PAST_DUE->needsPaymentAction())->toBeTrue();
            expect(SubscriptionStatus::UNPAID->needsPaymentAction())->toBeTrue();
            expect(SubscriptionStatus::INCOMPLETE->needsPaymentAction())->toBeTrue();

            expect(SubscriptionStatus::ACTIVE->needsPaymentAction())->toBeFalse();
            expect(SubscriptionStatus::TRIALING->needsPaymentAction())->toBeFalse();
        });

        it('provides human-readable labels', function () {
            expect(SubscriptionStatus::ACTIVE->label())->toBe('Active');
            expect(SubscriptionStatus::PAST_DUE->label())->toBe('Past Due');
            expect(SubscriptionStatus::INCOMPLETE_EXPIRED->label())->toBe('Expired');
        });

    });

    describe('TenantStatus Enum', function () {

        it('has all expected tenant statuses', function () {
            $expectedStatuses = [
                'active', 'inactive', 'suspended', 'pending', 'trial', 'trial_expired',
            ];

            foreach ($expectedStatuses as $status) {
                expect(TenantStatus::tryFrom($status))->not->toBeNull();
            }
        });

        it('correctly identifies access-allowing statuses', function () {
            expect(TenantStatus::ACTIVE->canAccess())->toBeTrue();
            expect(TenantStatus::TRIAL->canAccess())->toBeTrue();

            expect(TenantStatus::INACTIVE->canAccess())->toBeFalse();
            expect(TenantStatus::SUSPENDED->canAccess())->toBeFalse();
            expect(TenantStatus::TRIAL_EXPIRED->canAccess())->toBeFalse();
        });

        it('correctly identifies statuses needing attention', function () {
            expect(TenantStatus::TRIAL->needsAttention())->toBeTrue();
            expect(TenantStatus::TRIAL_EXPIRED->needsAttention())->toBeTrue();
            expect(TenantStatus::SUSPENDED->needsAttention())->toBeTrue();

            expect(TenantStatus::ACTIVE->needsAttention())->toBeFalse();
        });

        it('provides human-readable labels', function () {
            expect(TenantStatus::ACTIVE->label())->toBe('Active');
            expect(TenantStatus::TRIAL_EXPIRED->label())->toBe('Trial Expired');
            expect(TenantStatus::PENDING->label())->toBe('Pending Activation');
        });

    });

    describe('QuotaPeriod Enum', function () {

        it('has all expected period types', function () {
            $expectedPeriods = ['lifetime', 'monthly', 'yearly', 'daily', 'weekly'];

            foreach ($expectedPeriods as $period) {
                expect(QuotaPeriod::tryFrom($period))->not->toBeNull();
            }
        });

        it('calculates correct next reset dates', function () {
            $now = now();

            // Monthly should reset at start of next month
            $monthlyReset = QuotaPeriod::MONTHLY->getNextResetDate($now);
            expect($monthlyReset->isStartOfMonth())->toBeTrue();
            expect($monthlyReset->gt($now))->toBeTrue();

            // Yearly should reset at start of next year
            $yearlyReset = QuotaPeriod::YEARLY->getNextResetDate($now);
            expect($yearlyReset->isStartOfYear())->toBeTrue();
            expect($yearlyReset->gt($now))->toBeTrue();

            // Lifetime should never reset (null)
            $lifetimeReset = QuotaPeriod::LIFETIME->getNextResetDate($now);
            expect($lifetimeReset)->toBeNull();
        });

        it('correctly determines if reset is needed', function () {
            $pastDate = now()->subMonth();
            $futureDate = now()->addMonth();

            // Past reset date should trigger reset (except lifetime)
            expect(QuotaPeriod::MONTHLY->shouldReset($pastDate))->toBeTrue();
            expect(QuotaPeriod::YEARLY->shouldReset($pastDate))->toBeTrue();
            expect(QuotaPeriod::LIFETIME->shouldReset($pastDate))->toBeFalse();

            // Future reset date should not trigger reset
            expect(QuotaPeriod::MONTHLY->shouldReset($futureDate))->toBeFalse();
        });

        it('provides human-readable labels', function () {
            expect(QuotaPeriod::LIFETIME->label())->toBe('Lifetime');
            expect(QuotaPeriod::MONTHLY->label())->toBe('Monthly');
            expect(QuotaPeriod::YEARLY->label())->toBe('Yearly');
        });

    });

    describe('WebhookStatus Enum', function () {

        it('has all expected webhook statuses', function () {
            $expectedStatuses = ['pending', 'processing', 'processed', 'failed', 'skipped'];

            foreach ($expectedStatuses as $status) {
                expect(WebhookStatus::tryFrom($status))->not->toBeNull();
            }
        });

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
            expect(WebhookStatus::SKIPPED->canRetry())->toBeFalse();
            expect(WebhookStatus::PENDING->canRetry())->toBeFalse();
        });

        it('provides human-readable labels', function () {
            expect(WebhookStatus::PENDING->label())->toBe('Pending');
            expect(WebhookStatus::PROCESSING->label())->toBe('Processing');
            expect(WebhookStatus::PROCESSED->label())->toBe('Processed');
            expect(WebhookStatus::FAILED->label())->toBe('Failed');
        });

    });

});

describe('Enum Type Safety', function () {

    it('enums are backed by string values', function () {
        expect(SubscriptionStatus::ACTIVE->value)->toBe('active');
        expect(TenantStatus::ACTIVE->value)->toBe('active');
        expect(QuotaPeriod::MONTHLY->value)->toBe('monthly');
        expect(WebhookStatus::PROCESSED->value)->toBe('processed');
    });

    it('can be created from string values', function () {
        expect(SubscriptionStatus::from('active'))->toBe(SubscriptionStatus::ACTIVE);
        expect(TenantStatus::from('suspended'))->toBe(TenantStatus::SUSPENDED);
        expect(QuotaPeriod::from('yearly'))->toBe(QuotaPeriod::YEARLY);
        expect(WebhookStatus::from('failed'))->toBe(WebhookStatus::FAILED);
    });

    it('returns null for invalid values with tryFrom', function () {
        expect(SubscriptionStatus::tryFrom('invalid'))->toBeNull();
        expect(TenantStatus::tryFrom('unknown'))->toBeNull();
        expect(QuotaPeriod::tryFrom('hourly'))->toBeNull();
        expect(WebhookStatus::tryFrom('unknown'))->toBeNull();
    });

    it('throws exception for invalid values with from', function () {
        expect(fn() => SubscriptionStatus::from('invalid'))->toThrow(ValueError::class);
        expect(fn() => TenantStatus::from('invalid'))->toThrow(ValueError::class);
    });

});
