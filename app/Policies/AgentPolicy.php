<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;

class AgentPolicy
{
    /**
     * Determine whether the user can view any agents.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('manage-agents') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the agent.
     */
    public function view(User $user, Agent $agent): bool
    {
        return $user->hasPermission('manage-agents') || $user->isAdmin();
    }

    /**
     * Determine whether the user can create agents.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-agents') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the agent.
     */
    public function update(User $user, Agent $agent): bool
    {
        return $user->hasPermission('manage-agents') || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the agent.
     */
    public function delete(User $user, Agent $agent): bool
    {
        return $user->hasPermission('manage-agents') || $user->isAdmin();
    }

    /**
     * Determine whether the user can view agent commissions.
     */
    public function viewCommissions(User $user, Agent $agent): bool
    {
        return $user->hasPermission('view-commissions') || $user->isAdmin();
    }

    /**
     * Determine whether the user can approve commissions.
     */
    public function approveCommissions(User $user): bool
    {
        return $user->hasPermission('approve-commissions') || $user->isAdmin();
    }

    /**
     * Determine whether the user can mark commissions as paid.
     */
    public function markCommissionsPaid(User $user): bool
    {
        return $user->hasPermission('approve-commissions') || $user->isAdmin();
    }
}
