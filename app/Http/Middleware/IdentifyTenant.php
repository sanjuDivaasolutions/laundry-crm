<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->identifyTenant($request);

        if ($tenant) {
            // Store tenant in request for downstream usage
            $request->merge(['tenant_id' => $tenant->id]);
            $request->attributes->set('tenant', $tenant);
        }

        return $next($request);
    }

    /**
     * Identify tenant from request (subdomain or header)
     */
    protected function identifyTenant(Request $request): ?Tenant
    {
        // 1. Check for X-Tenant-ID header (for API clients)
        if ($request->hasHeader('X-Tenant-ID')) {
            return Tenant::find($request->header('X-Tenant-ID'));
        }

        // 2. Check for subdomain (e.g., tenant1.app.com)
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0] ?? null;

        if ($subdomain && $subdomain !== 'www') {
            return Tenant::where('domain', $subdomain)
                ->where('active', true)
                ->first();
        }

        return null;
    }
}
