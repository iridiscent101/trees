<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\VerificationStatus;
use App\Models\TreeMonitoring;
use App\Models\User;

class TreeMonitoringPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewMonitoring->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TreeMonitoring $treeMonitoring): bool
    {
        return $user->can(PermissionName::ViewMonitoring->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateMonitoring->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TreeMonitoring $treeMonitoring): bool
    {
        return $user->can(PermissionName::CreateMonitoring->value)
            && in_array($treeMonitoring->verification_status, [VerificationStatus::Pending, VerificationStatus::NeedsCorrection], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TreeMonitoring $treeMonitoring): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TreeMonitoring $treeMonitoring): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TreeMonitoring $treeMonitoring): bool
    {
        return false;
    }
}
