<?php

declare(strict_types=1);

use App\Models\Plan;

describe('Plan Model', function () {
    describe('Price Formatting', function () {
        it('formats USD price correctly', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'price_yearly' => 29990,
                'currency' => 'usd',
            ]);

            expect($plan->getFormattedPrice('monthly'))->toBe('$29.99');
            expect($plan->getFormattedPrice('yearly'))->toBe('$299.90');
        });

        it('formats EUR price correctly', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'currency' => 'eur',
            ]);

            expect($plan->getFormattedPrice('monthly'))->toBe('€29.99');
        });

        it('formats GBP price correctly', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'currency' => 'gbp',
            ]);

            expect($plan->getFormattedPrice('monthly'))->toBe('£29.99');
        });

        it('formats unknown currency with code', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'currency' => 'jpy',
            ]);

            expect($plan->getFormattedPrice('monthly'))->toBe('JPY 29.99');
        });
    });

    describe('Stripe Price ID Resolution', function () {
        it('returns monthly price ID for monthly period', function () {
            $plan = new Plan([
                'stripe_price_id' => 'price_monthly_123',
                'stripe_yearly_price_id' => 'price_yearly_456',
            ]);

            expect($plan->getStripePriceId('monthly'))->toBe('price_monthly_123');
        });

        it('returns yearly price ID for yearly period', function () {
            $plan = new Plan([
                'stripe_price_id' => 'price_monthly_123',
                'stripe_yearly_price_id' => 'price_yearly_456',
            ]);

            expect($plan->getStripePriceId('yearly'))->toBe('price_yearly_456');
        });

        it('falls back to monthly price if yearly not set', function () {
            $plan = new Plan([
                'stripe_price_id' => 'price_monthly_123',
                'stripe_yearly_price_id' => null,
            ]);

            expect($plan->getStripePriceId('yearly'))->toBe('price_monthly_123');
        });

        it('returns monthly price for unknown period', function () {
            $plan = new Plan([
                'stripe_price_id' => 'price_monthly_123',
                'stripe_yearly_price_id' => 'price_yearly_456',
            ]);

            expect($plan->getStripePriceId('weekly'))->toBe('price_monthly_123');
        });
    });

    describe('Price Retrieval', function () {
        it('returns correct price for monthly period', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'price_yearly' => 29990,
            ]);

            expect($plan->getPrice('monthly'))->toBe(2999);
        });

        it('returns correct price for yearly period', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'price_yearly' => 29990,
            ]);

            expect($plan->getPrice('yearly'))->toBe(29990);
        });

        it('returns correct price for annual alias', function () {
            $plan = new Plan([
                'price_monthly' => 2999,
                'price_yearly' => 29990,
            ]);

            expect($plan->getPrice('annual'))->toBe(29990);
        });
    });
});

describe('Plan Attributes', function () {
    it('casts price_monthly to integer', function () {
        $plan = new Plan(['price_monthly' => '2999']);
        expect($plan->price_monthly)->toBeInt()->toBe(2999);
    });

    it('casts price_yearly to integer', function () {
        $plan = new Plan(['price_yearly' => '29990']);
        expect($plan->price_yearly)->toBeInt()->toBe(29990);
    });

    it('casts trial_days to integer', function () {
        $plan = new Plan(['trial_days' => '14']);
        expect($plan->trial_days)->toBeInt()->toBe(14);
    });

    it('casts is_active to boolean', function () {
        $plan = new Plan(['is_active' => 1]);
        expect($plan->is_active)->toBeBool()->toBeTrue();
    });

    it('casts is_featured to boolean', function () {
        $plan = new Plan(['is_featured' => 0]);
        expect($plan->is_featured)->toBeBool()->toBeFalse();
    });
});
