<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\TenantResolutionException;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to identify and set the current tenant context.
 *
 * Security Model:
 * - Authenticated users: ALWAYS use their assigned tenant_id (no override)
 * - Unauthenticated requests: Use domain matching only
 * - Internal services: Can use X-Tenant-ID with signed verification
 * - Super-admins: Can impersonate with explicit permission check
 *
 * NEVER trust client-provided tenant ID without verification.
 */
class IdentifyTenant
{
    /**
     * Header name for internal service tenant identification.
     */
    private const TENANT_HEADER = 'X-Tenant-ID';

    /**
     * Header name for internal service signature verification.
     */
    private const SIGNATURE_HEADER = 'X-Tenant-Signature';

    /**
     * Header name for super-admin impersonation.
     */
    private const IMPERSONATE_HEADER = 'X-Impersonate-Tenant';

    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            if (!$tenant->active) {
                return $this->tenantInactiveResponse($tenant);
            }

            $this->tenantService->setTenant($tenant);

            logger()->debug('Tenant context established', [
                'tenant_id' => $tenant->id,
                'resolution_method' => $request->attributes->get('tenant_resolution_method', 'unknown'),
            ]);
        }

        return $next($request);
    }

    /**
     * Resolve tenant using secure resolution chain.
     *
     * Priority:
     * 1. Authenticated user's tenant (mandatory for auth requests)
     * 2. Super-admin impersonation (requires permission)
     * 3. Internal service header (requires signature)
     * 4. Domain matching (for public routes)
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // 1. HIGHEST PRIORITY: Authenticated user's tenant
        // This CANNOT be overridden by any header
        if ($tenant = $this->resolveFromAuthenticatedUser($request)) {
            $request->attributes->set('tenant_resolution_method', 'authenticated_user');
            return $tenant;
        }

        // 2. Super-admin impersonation (only if authenticated AND has permission)
        if ($tenant = $this->resolveFromImpersonation($request)) {
            $request->attributes->set('tenant_resolution_method', 'super_admin_impersonation');
            return $tenant;
        }

        // 3. Internal service-to-service communication (requires signed header)
        if ($tenant = $this->resolveFromInternalService($request)) {
            $request->attributes->set('tenant_resolution_method', 'internal_service');
            return $tenant;
        }

        // 4. Domain matching (for public/unauthenticated routes)
        if ($tenant = $this->resolveFromDomain($request)) {
            $request->attributes->set('tenant_resolution_method', 'domain');
            return $tenant;
        }

        return null;
    }

    /**
     * Resolve tenant from authenticated user.
     * This is the PRIMARY and MANDATORY source for authenticated requests.
     */
    protected function resolveFromAuthenticatedUser(Request $request): ?Tenant
    {
        try {
            $user = adminAuth()->user();

            if (!$user) {
                return null;
            }

            // User MUST have a tenant_id
            if (!$user->tenant_id) {
                logger()->warning('Authenticated user without tenant_id', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                return null;
            }

            // Load and return the user's tenant
            // Use withoutGlobalScope to bypass the tenant scope while we are identifying the tenant
            $user = User::withoutGlobalScope('tenant')->find($user->id);

            return $user ? Tenant::find($user->tenant_id) : null;
        } catch (\Exception $e) {
            // Auth not yet verified - this is fine, will fall through
            return null;
        }
    }

    /**
     * Resolve tenant from super-admin impersonation.
     *
     * Requirements:
     * - User must be authenticated
     * - User must have 'impersonate_tenant' permission
     * - Target tenant must exist and be active
     */
    protected function resolveFromImpersonation(Request $request): ?Tenant
    {
        $impersonateTenantId = $request->header(self::IMPERSONATE_HEADER);

        if (!$impersonateTenantId) {
            return null;
        }

        try {
            $user = adminAuth()->user();

            if (!$user) {
                logger()->warning('Impersonation attempt without authentication', [
                    'target_tenant_id' => $impersonateTenantId,
                    'ip' => $request->ip(),
                ]);
                return null;
            }

            // Check for super-admin impersonation permission
            if (!$user->hasPermission('impersonate_tenant') && !$this->isSystemAdmin($user)) {
                logger()->warning('Unauthorized impersonation attempt', [
                    'user_id' => $user->id,
                    'target_tenant_id' => $impersonateTenantId,
                    'ip' => $request->ip(),
                ]);
                return null;
            }

            $tenant = Tenant::find($impersonateTenantId);

            if ($tenant) {
                logger()->info('Super-admin tenant impersonation', [
                    'admin_user_id' => $user->id,
                    'admin_tenant_id' => $user->tenant_id,
                    'impersonated_tenant_id' => $tenant->id,
                    'ip' => $request->ip(),
                ]);
            }

            return $tenant;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Resolve tenant from internal service-to-service call.
     *
     * Requirements:
     * - Must include X-Tenant-ID header
     * - Must include valid X-Tenant-Signature header
     * - Signature must be valid HMAC of tenant_id + timestamp
     */
    protected function resolveFromInternalService(Request $request): ?Tenant
    {
        $tenantId = $request->header(self::TENANT_HEADER);
        $signature = $request->header(self::SIGNATURE_HEADER);

        if (!$tenantId || !$signature) {
            return null;
        }

        // Verify the signature
        if (!$this->verifyInternalServiceSignature($tenantId, $signature)) {
            logger()->warning('Invalid internal service signature', [
                'tenant_id' => $tenantId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return null;
        }

        return Tenant::find($tenantId);
    }

    /**
     * Verify internal service signature.
     *
     * Signature format: HMAC-SHA256(tenant_id:timestamp, secret)
     * Timestamp must be within 5 minutes of current time.
     */
    protected function verifyInternalServiceSignature(string $tenantId, string $signature): bool
    {
        $secret = config('services.internal.secret');

        if (!$secret) {
            // Internal service communication not configured
            return false;
        }

        // Signature format: base64(hmac):timestamp
        $parts = explode(':', $signature);

        if (count($parts) !== 2) {
            return false;
        }

        [$providedHmac, $timestamp] = $parts;

        // Verify timestamp is within 5 minutes
        $timestampInt = (int) $timestamp;
        $currentTime = time();

        if (abs($currentTime - $timestampInt) > 300) {
            logger()->warning('Internal service signature expired', [
                'tenant_id' => $tenantId,
                'timestamp' => $timestamp,
                'current_time' => $currentTime,
            ]);
            return false;
        }

        // Compute expected signature
        $payload = "{$tenantId}:{$timestamp}";
        $expectedHmac = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($expectedHmac, $providedHmac);
    }

    /**
     * Resolve tenant from domain/subdomain.
     * Only used for unauthenticated requests.
     */
    protected function resolveFromDomain(Request $request): ?Tenant
    {
        $host = $request->getHost();

        // Try exact domain match
        $tenant = Tenant::where('domain', $host)->first();

        if ($tenant) {
            return $tenant;
        }

        // Try subdomain extraction (e.g., tenant.example.com)
        $baseDomain = config('tenancy.base_domain');

        if ($baseDomain && str_ends_with($host, ".{$baseDomain}")) {
            $subdomain = str_replace(".{$baseDomain}", '', $host);
            return Tenant::where('domain', $subdomain)->first();
        }

        // 5. LOCALHOST FALLBACK (for development)
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return Tenant::first(); // Fallback to first tenant for local dev
        }

        return null;
    }

    /**
     * Check if user is a system administrator.
     */
    protected function isSystemAdmin($user): bool
    {
        $systemAdminIds = config('system.auth.admin_user_id', []);
        return in_array($user->id, $systemAdminIds);
    }

    /**
     * Return response for inactive tenant.
     */
    protected function tenantInactiveResponse(Tenant $tenant): Response
    {
        logger()->warning('Access attempt to inactive tenant', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);

        return response()->json([
            'success' => false,
            'error' => 'tenant_inactive',
            'message' => 'This account has been deactivated. Please contact support.',
        ], 403);
    }
}
