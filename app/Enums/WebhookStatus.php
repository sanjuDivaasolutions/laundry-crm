<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Webhook processing status enum.
 */
enum WebhookStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    /**
     * Check if webhook is in a terminal state.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::PROCESSED, self::FAILED, self::SKIPPED => true,
            default => false,
        };
    }

    /**
     * Check if webhook can be retried.
     */
    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::PROCESSED => 'Processed',
            self::FAILED => 'Failed',
            self::SKIPPED => 'Skipped',
        };
    }
}
