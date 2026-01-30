<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * TenantSettingsController
 *
 * Handles tenant settings and profile management.
 */
class TenantSettingsController extends Controller
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Get all settings for the current tenant.
     */
    public function index(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (! $tenant) {
            return $this->error('No tenant context', 403);
        }

        $settings = TenantSetting::getAllForTenant($tenant->id);

        return $this->success(['settings' => $settings]);
    }

    /**
     * Update settings for the current tenant.
     */
    public function update(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (! $tenant) {
            return $this->error('No tenant context', 403);
        }

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['required'],
            'settings.*.type' => ['nullable', 'string', 'in:string,int,float,bool,json'],
            'settings.*.group' => ['nullable', 'string'],
        ]);

        foreach ($validated['settings'] as $setting) {
            TenantSetting::setValue(
                $tenant->id,
                $setting['key'],
                $setting['value'],
                $setting['type'] ?? 'string',
                $setting['group'] ?? 'general'
            );
        }

        return $this->success(null, 'Settings updated successfully.');
    }

    /**
     * Get the current tenant's profile.
     */
    public function profile(): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (! $tenant) {
            return $this->error('No tenant context', 403);
        }

        return $this->success([
            'profile' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'url' => $tenant->getUrl(),
                'timezone' => $tenant->timezone,
                'currency' => $tenant->currency,
                'logo_path' => $tenant->logo_path,
                'created_at' => $tenant->created_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Update the current tenant's profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (! $tenant) {
            return $this->error('No tenant context', 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'timezone' => ['sometimes', 'timezone'],
            'currency' => ['sometimes', 'string', 'size:3'],
        ]);

        $tenant->update($validated);

        return $this->success([
            'profile' => $tenant->fresh(),
        ], 'Profile updated successfully.');
    }

    /**
     * Upload a logo for the tenant.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $tenant = $this->tenantService->getTenant();

        if (! $tenant) {
            return $this->error('No tenant context', 403);
        }

        $request->validate([
            'logo' => ['required', 'image', 'max:2048'], // Max 2MB
        ]);

        $path = $request->file('logo')->store("tenants/{$tenant->id}", 'public');

        $tenant->update(['logo_path' => $path]);

        return $this->success([
            'logo_url' => asset('storage/'.$path),
        ], 'Logo uploaded successfully.');
    }
}
