<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\CampusArea;
use App\Models\User;

class CampusAreaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageCampusAreas->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CampusArea $campusArea): bool
    {
        return $user->can(PermissionName::ManageCampusAreas->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageCampusAreas->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CampusArea $campusArea): bool
    {
        return $user->can(PermissionName::ManageCampusAreas->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CampusArea $campusArea): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CampusArea $campusArea): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CampusArea $campusArea): bool
    {
        return false;
    }
}
