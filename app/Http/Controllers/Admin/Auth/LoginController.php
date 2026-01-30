<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\TenantService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Get current tenant context (if any - from subdomain resolution)
        $currentTenant = $this->tenantService->getTenant();

        // First check if the user exists and belongs to the correct tenant
        if ($currentTenant) {
            // When accessed via a tenant subdomain, user MUST belong to that tenant
            $user = User::withoutGlobalScope('tenant')
                ->where('email', $request->input('email'))
                ->where('tenant_id', $currentTenant->id)
                ->first();

            if (! $user) {
                // Don't reveal whether user exists in another tenant
                logger()->warning('Login attempt for user not in tenant', [
                    'email' => $request->input('email'),
                    'tenant_id' => $currentTenant->id,
                    'ip' => $request->ip(),
                ]);

                return $this->error('Invalid Email or Password', 401);
            }

            // Check if user is active
            if (! $user->active) {
                logger()->warning('Login attempt for inactive user', [
                    'user_id' => $user->id,
                    'tenant_id' => $currentTenant->id,
                    'ip' => $request->ip(),
                ]);

                return $this->error('Your account has been deactivated. Please contact support.', 401);
            }
        }

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        $credentials['active'] = 1;

        // Add tenant scope to credentials if in tenant context
        if ($currentTenant) {
            $credentials['tenant_id'] = $currentTenant->id;
        }

        $token = auth('admin')->attempt($credentials);

        if (! $token) {
            logger()->info('Failed login attempt', [
                'email' => $request->input('email'),
                'tenant_id' => $currentTenant?->id,
                'ip' => $request->ip(),
            ]);

            return $this->error('Invalid Email or Password', 401);
        }

        // Set tenant context from authenticated user for subsequent middleware
        $user = auth('admin')->user();
        if ($user && $user->tenant_id && ! $currentTenant) {
            $tenant = \App\Models\Tenant::find($user->tenant_id);
            if ($tenant) {
                $this->tenantService->setTenant($tenant);
            }
        }

        logger()->info('Successful login', [
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id ?? null,
            'ip' => $request->ip(),
        ]);

        return $this->success(AuthService::getUserResponse());
    }
}
