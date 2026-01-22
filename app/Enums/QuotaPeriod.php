<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\Carbon;

/**
 * Quota period enum for usage tracking reset cycles.
 */
enum QuotaPeriod: string
{
    case LIFETIME = 'lifetime';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case DAILY = 'daily';
    case WEEKLY = 'weekly';

    /**
     * Calculate the next reset date based on period.
     */
    public function getNextResetDate(?Carbon $from = null): ?Carbon
    {
        $from = $from ?? Carbon::now();

        return match ($this) {
            self::LIFETIME => null, // Never resets
            self::DAILY => $from->copy()->addDay()->startOfDay(),
            self::WEEKLY => $from->copy()->addWeek()->startOfWeek(),
            self::MONTHLY => $from->copy()->addMonth()->startOfMonth(),
            self::YEARLY => $from->copy()->addYear()->startOfYear(),
        };
    }

    /**
     * Check if a reset date has passed and needs reset.
     */
    public function shouldReset(?Carbon $resetAt): bool
    {
        if ($this === self::LIFETIME) {
            return false;
        }

        if (!$resetAt) {
            return true;
        }

        return $resetAt->isPast();
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LIFETIME => 'Lifetime',
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::YEARLY => 'Yearly',
        };
    }
}
