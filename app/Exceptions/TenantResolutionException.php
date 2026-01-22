<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when tenant resolution fails.
 */
class TenantResolutionException extends Exception
{
    public function __construct(
        string $message = 'Unable to resolve tenant context',
        int $code = 403,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception for missing tenant context.
     */
    public static function missingContext(): self
    {
        return new self('Tenant context is required but not available');
    }

    /**
     * Create exception for inactive tenant.
     */
    public static function inactive(int $tenantId): self
    {
        return new self("Tenant {$tenantId} is inactive");
    }

    /**
     * Create exception for unauthorized access attempt.
     */
    public static function unauthorized(int $userId, int $tenantId): self
    {
        return new self("User {$userId} is not authorized to access tenant {$tenantId}");
    }
}
