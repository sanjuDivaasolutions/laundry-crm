<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-companies') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        // Users can view their own company
        if ($user->company_id) {
            return $user->company_id === $company->id || 
                   $user->hasPermission('manage-companies') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-companies') || $user->isAdmin();
    }

    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-companies') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        // Users can update their own company
        if ($user->company_id) {
            return $user->company_id === $company->id || 
                   $user->hasPermission('manage-companies') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-companies') || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        // Users can delete their own company
        if ($user->company_id) {
            return $user->company_id === $company->id || 
                   $user->hasPermission('manage-companies') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('manage-companies') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view company reports.
     */
    public function viewReports(User $user, Company $company): bool
    {
        // Users can view reports for their own company
        if ($user->company_id) {
            return $user->company_id === $company->id || 
                   $user->hasPermission('view-reports') || 
                   $user->isAdmin();
        }

        return $user->hasPermission('view-reports') || $user->isAdmin();
    }
}