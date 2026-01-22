<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tenant account status enum.
 */
enum TenantStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';
    case TRIAL = 'trial';
    case TRIAL_EXPIRED = 'trial_expired';

    /**
     * Check if tenant can access the platform.
     */
    public function canAccess(): bool
    {
        return match ($this) {
            self::ACTIVE, self::TRIAL => true,
            default => false,
        };
    }

    /**
     * Check if tenant is in a warning state.
     */
    public function needsAttention(): bool
    {
        return match ($this) {
            self::TRIAL, self::TRIAL_EXPIRED, self::SUSPENDED => true,
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
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending Activation',
            self::TRIAL => 'Trial',
            self::TRIAL_EXPIRED => 'Trial Expired',
        };
    }
}
