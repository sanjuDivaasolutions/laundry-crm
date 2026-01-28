<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateSessionTenant Middleware
 *
 * Provides additional session-based tenant validation as defense in depth.
 * This middleware ensures that if a session has a tenant context, it matches
 * the current resolved tenant context.
 *
 * Security Features:
 * - Prevents session fixation attacks across tenants
 * - Detects and blocks cross-tenant session hijacking attempts
 * - Logs security incidents for monitoring
 *
 * Note: This is complementary to JWT-based auth, not a replacement.
 */
class ValidateSessionTenant
{
    /**
     * Session key for storing tenant ID.
     */
    private const SESSION_TENANT_KEY = 'tenant_id';

    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if session validation is disabled
        if (!config('tenancy.session.validate_tenant', true)) {
            return $next($request);
        }

        $currentTenant = $this->tenantService->getTenant();
        $sessionTenantId = $request->session()->get(self::SESSION_TENANT_KEY);

        // If no current tenant context, nothing to validate against
        if (!$currentTenant) {
            return $next($request);
        }

        // If session has a tenant ID and it doesn't match current tenant
        if ($sessionTenantId !== null && $sessionTenantId !== $currentTenant->id) {
            // Log the security incident
            logger()->warning('Session tenant mismatch detected', [
                'session_tenant_id' => $sessionTenantId,
                'current_tenant_id' => $currentTenant->id,
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);

            // In strict mode, invalidate the session and force re-authentication
            if (config('tenancy.session.strict_validation', true)) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'success' => false,
                    'error' => 'session_invalid',
                    'message' => 'Your session has expired. Please log in again.',
                ], 401);
            }
        }

        // Store current tenant ID in session for future validation
        if ($currentTenant && $sessionTenantId === null) {
            $request->session()->put(self::SESSION_TENANT_KEY, $currentTenant->id);
        }

        return $next($request);
    }
}
