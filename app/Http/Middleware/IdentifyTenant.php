<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            $this->tenantService->setTenant($tenant);
        }

        return $next($request);
    }

    protected function resolveTenant(Request $request): ?Tenant
    {
        // 1. Try Header (X-Tenant-ID)
        if ($tenantId = $request->header('X-Tenant-ID')) {
            return Tenant::find($tenantId);
        }

        // 2. Try Domain/Subdomain
        $host = $request->getHost();
        if ($tenant = Tenant::where('domain', $host)->first()) {
            return $tenant;
        }

        // 3. Fallback (optional: could check query param or authenticated user's tenant)
        
        return null;
    }
}
