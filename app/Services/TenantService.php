<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

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
            logger()->shareContext(['tenant_id' => $tenant->id]);
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
