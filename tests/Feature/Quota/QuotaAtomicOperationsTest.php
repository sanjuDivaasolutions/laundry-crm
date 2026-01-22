<?php

declare(strict_types=1);

use App\Enums\QuotaPeriod;
use App\Models\Tenant;
use App\Models\TenantQuota;
use App\Models\TenantUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create([
        'name' => 'Test Tenant',
        'active' => true,
    ]);
});

describe('SEC-003: Quota Race Condition Prevention', function () {

    it('atomically increments usage within limit', function () {
        // Set up a quota with limit of 10
        $this->tenant->setQuota('api_calls', 10, QuotaPeriod::MONTHLY);

        // Track 5 units of usage
        $result = $this->tenant->trackUsage('api_calls', 5);

        expect($result)->toBeTrue();
        expect($this->tenant->getUsage('api_calls'))->toBe(5);
    });

    it('rejects usage that would exceed limit', function () {
        $this->tenant->setQuota('api_calls', 10, QuotaPeriod::MONTHLY);

        // First track 8 units
        $this->tenant->trackUsage('api_calls', 8);

        // Try to track 5 more (would exceed limit of 10)
        $result = $this->tenant->trackUsage('api_calls', 5);

        expect($result)->toBeFalse();
        expect($this->tenant->getUsage('api_calls'))->toBe(8); // Unchanged
    });

    it('handles concurrent requests atomically', function () {
        $this->tenant->setQuota('concurrent_test', 10, QuotaPeriod::MONTHLY);

        // Simulate concurrent requests using database transactions
        $successCount = 0;
        $attempts = 15;

        for ($i = 0; $i < $attempts; $i++) {
            if ($this->tenant->trackUsage('concurrent_test', 1)) {
                $successCount++;
            }
        }

        // Only 10 should succeed (the limit)
        expect($successCount)->toBe(10);
        expect($this->tenant->getUsage('concurrent_test'))->toBe(10);
    });

    it('allows unlimited quota tracking for analytics', function () {
        $this->tenant->setQuota('unlimited_feature', -1, QuotaPeriod::LIFETIME);

        // Track large amounts - should always succeed
        $result1 = $this->tenant->trackUsage('unlimited_feature', 1000);
        $result2 = $this->tenant->trackUsage('unlimited_feature', 5000);

        expect($result1)->toBeTrue();
        expect($result2)->toBeTrue();
        expect($this->tenant->getUsage('unlimited_feature'))->toBe(6000);
    });

    it('blocks all usage when limit is zero', function () {
        $this->tenant->setQuota('disabled_feature', 0, QuotaPeriod::MONTHLY);

        $result = $this->tenant->trackUsage('disabled_feature', 1);

        expect($result)->toBeFalse();
        expect($this->tenant->getUsage('disabled_feature'))->toBe(0);
    });

    it('correctly checks if quota would be exceeded', function () {
        $this->tenant->setQuota('check_test', 5, QuotaPeriod::MONTHLY);
        $this->tenant->trackUsage('check_test', 3);

        // Would NOT exceed (3 + 2 = 5, equals limit)
        expect($this->tenant->couldExceedQuota('check_test', 2))->toBeFalse();

        // WOULD exceed (3 + 3 = 6, over limit)
        expect($this->tenant->couldExceedQuota('check_test', 3))->toBeTrue();
    });

    it('decrements usage atomically', function () {
        $this->tenant->setQuota('decrement_test', 10, QuotaPeriod::MONTHLY);
        $this->tenant->trackUsage('decrement_test', 8);

        // Decrement by 3
        $result = $this->tenant->decrementUsage('decrement_test', 3);

        expect($result)->toBeTrue();
        expect($this->tenant->getUsage('decrement_test'))->toBe(5);
    });

    it('prevents decrementing below zero', function () {
        $this->tenant->setQuota('decrement_below_test', 10, QuotaPeriod::MONTHLY);
        $this->tenant->trackUsage('decrement_below_test', 3);

        // Try to decrement by more than current usage
        $result = $this->tenant->decrementUsage('decrement_below_test', 5);

        expect($result)->toBeFalse();
        expect($this->tenant->getUsage('decrement_below_test'))->toBe(3); // Unchanged
    });

});

describe('Quota Period Management', function () {

    it('auto-resets usage when period expires', function () {
        $this->tenant->setQuota('daily_quota', 100, QuotaPeriod::DAILY);

        // Create usage with past reset date
        TenantUsage::create([
            'tenant_id' => $this->tenant->id,
            'quota_code' => 'daily_quota',
            'current_usage' => 50,
            'reset_at' => now()->subDays(1), // Yesterday
        ]);

        // getUsage should detect expired period and reset
        $usage = $this->tenant->getUsage('daily_quota');

        expect($usage)->toBe(0);
    });

    it('calculates correct next reset date for monthly quota', function () {
        $period = QuotaPeriod::MONTHLY;
        $nextReset = $period->getNextResetDate();

        expect($nextReset->isStartOfMonth())->toBeTrue();
        expect($nextReset->month)->toBe(now()->addMonth()->month);
    });

    it('returns null reset date for lifetime quota', function () {
        $period = QuotaPeriod::LIFETIME;
        $nextReset = $period->getNextResetDate();

        expect($nextReset)->toBeNull();
    });

    it('resets all periodic quotas on demand', function () {
        // Set up multiple quotas
        $this->tenant->setQuota('monthly_quota', 100, QuotaPeriod::MONTHLY);
        $this->tenant->setQuota('yearly_quota', 1000, QuotaPeriod::YEARLY);
        $this->tenant->setQuota('lifetime_quota', -1, QuotaPeriod::LIFETIME);

        // Track usage on all
        $this->tenant->trackUsage('monthly_quota', 50);
        $this->tenant->trackUsage('yearly_quota', 500);
        $this->tenant->trackUsage('lifetime_quota', 100);

        // Reset periodic quotas
        $resetCount = $this->tenant->resetAllPeriodicQuotas();

        expect($resetCount)->toBe(2); // Monthly and yearly, not lifetime
        expect($this->tenant->getUsage('monthly_quota'))->toBe(0);
        expect($this->tenant->getUsage('yearly_quota'))->toBe(0);
        expect($this->tenant->getUsage('lifetime_quota'))->toBe(100); // Unchanged
    });

});

describe('Quota Summary and Reporting', function () {

    it('generates accurate quota summary', function () {
        $this->tenant->setQuota('storage', 100, QuotaPeriod::MONTHLY);
        $this->tenant->setQuota('users', 10, QuotaPeriod::LIFETIME);
        $this->tenant->setQuota('api_calls', -1, QuotaPeriod::MONTHLY); // Unlimited

        $this->tenant->trackUsage('storage', 75);
        $this->tenant->trackUsage('users', 5);
        $this->tenant->trackUsage('api_calls', 1000);

        $summary = $this->tenant->getQuotaSummary();

        expect($summary['storage'])->toMatchArray([
            'usage' => 75,
            'limit' => 100,
            'remaining' => 25,
            'percentage' => 75.0,
            'is_unlimited' => false,
            'is_exceeded' => false,
        ]);

        expect($summary['users'])->toMatchArray([
            'usage' => 5,
            'limit' => 10,
            'remaining' => 5,
            'percentage' => 50.0,
            'is_exceeded' => false,
        ]);

        expect($summary['api_calls'])->toMatchArray([
            'is_unlimited' => true,
            'remaining' => 'unlimited',
        ]);
    });

    it('correctly identifies exceeded quotas', function () {
        $this->tenant->setQuota('exceeded_test', 5, QuotaPeriod::MONTHLY);
        $this->tenant->trackUsage('exceeded_test', 5);

        $summary = $this->tenant->getQuotaSummary();

        expect($summary['exceeded_test']['is_exceeded'])->toBeTrue();
        expect($summary['exceeded_test']['remaining'])->toBe(0);
        expect($summary['exceeded_test']['percentage'])->toBe(100.0);
    });

    it('returns remaining quota correctly', function () {
        $this->tenant->setQuota('remaining_test', 100, QuotaPeriod::MONTHLY);
        $this->tenant->trackUsage('remaining_test', 30);

        $remaining = $this->tenant->getRemainingQuota('remaining_test');

        expect($remaining)->toBe(70);
    });

    it('returns max int for unlimited quota remaining', function () {
        $this->tenant->setQuota('unlimited_remaining', -1, QuotaPeriod::LIFETIME);

        $remaining = $this->tenant->getRemainingQuota('unlimited_remaining');

        expect($remaining)->toBe(PHP_INT_MAX);
    });

});

describe('Feature Flag Management', function () {

    it('enables and checks feature flags', function () {
        $this->tenant->enableFeature('advanced_reporting');

        expect($this->tenant->canUse('advanced_reporting'))->toBeTrue();
        expect($this->tenant->canUse('unknown_feature'))->toBeFalse();
    });

    it('respects feature expiration dates', function () {
        // Enable feature that expired yesterday
        $this->tenant->enableFeature('trial_feature', now()->subDay());

        expect($this->tenant->canUse('trial_feature'))->toBeFalse();

        // Enable feature that expires tomorrow
        $this->tenant->enableFeature('active_feature', now()->addDay());

        expect($this->tenant->canUse('active_feature'))->toBeTrue();
    });

    it('disables features correctly', function () {
        $this->tenant->enableFeature('to_disable');
        expect($this->tenant->canUse('to_disable'))->toBeTrue();

        $this->tenant->disableFeature('to_disable');
        expect($this->tenant->canUse('to_disable'))->toBeFalse();
    });

});
