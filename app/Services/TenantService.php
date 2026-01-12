<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class TenantService
{
    protected ?Tenant $tenant = null;

    /**
     * Set the current tenant.
     */
    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
        
        if ($tenant) {
             // Share tenant context with logs
             Log::shareContext(['tenant_id' => $tenant->id]);
        }
    }

    /**
     * Get the current tenant.
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get the current tenant ID.
     */
    public function getId(): ?int
    {
        return $this->tenant?->id;
    }
}
