<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Subscription status enum matching Stripe subscription statuses.
 */
enum SubscriptionStatus: string
{
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case UNPAID = 'unpaid';
    case CANCELED = 'canceled';
    case INCOMPLETE = 'incomplete';
    case INCOMPLETE_EXPIRED = 'incomplete_expired';
    case TRIALING = 'trialing';
    case PAUSED = 'paused';

    /**
     * Check if status allows access to paid features.
     */
    public function allowsAccess(): bool
    {
        return match ($this) {
            self::ACTIVE, self::TRIALING, self::PAST_DUE => true,
            default => false,
        };
    }

    /**
     * Check if subscription is in a grace period.
     */
    public function isGracePeriod(): bool
    {
        return $this === self::PAST_DUE;
    }

    /**
     * Check if subscription needs payment action.
     */
    public function needsPaymentAction(): bool
    {
        return match ($this) {
            self::PAST_DUE, self::UNPAID, self::INCOMPLETE => true,
            default => false,
        };
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::PAST_DUE => 'Past Due',
            self::UNPAID => 'Unpaid',
            self::CANCELED => 'Canceled',
            self::INCOMPLETE => 'Incomplete',
            self::INCOMPLETE_EXPIRED => 'Expired',
            self::TRIALING => 'Trial',
            self::PAUSED => 'Paused',
        };
    }
}
