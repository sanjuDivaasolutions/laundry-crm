<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantService;

class EnforceTenantQuota
{
    public function handle(Request $request, Closure $next, string $quotaCode, int $amount = 1): Response
    {
        $tenant = app(TenantService::class)->getTenant();

        if (!$tenant) {
            // Should be handled by IdentifyTenant, but safety check
            abort(401, 'No tenant identified.');
        }

        // Check if the action WOULD exceed the quota
        // Note: For strict implementation, we might want to check against (current + amount)
        // Here we just check if they are ALREADY at/over limit logic via trait
        if ($tenant->couldExceedQuota($quotaCode, $amount)) {
            return response()->json([
                'error' => 'quota_exceeded',
                'message' => "You have exceeded the limit for {$quotaCode}. Please upgrade your plan.",
                'quota' => $quotaCode
            ], 403);
        }

        return $next($request);
    }
}
