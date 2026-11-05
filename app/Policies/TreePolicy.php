<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\Tree;
use App\Models\User;

class TreePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function view(User $user, Tree $tree): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::CreateTrees->value);
    }

    public function update(User $user, Tree $tree): bool
    {
        return $user->can(PermissionName::UpdateTrees->value);
    }

    public function delete(User $user, Tree $tree): bool
    {
        return $user->can(PermissionName::ArchiveTrees->value);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::ArchiveTrees->value);
    }

    public function restore(User $user, Tree $tree): bool
    {
        return $user->can(PermissionName::RestoreTrees->value);
    }

    public function restoreAny(User $user): bool
    {
        return $user->can(PermissionName::RestoreTrees->value);
    }

    public function forceDelete(User $user, Tree $tree): bool
    {
        return $user->hasRole(RoleName::Administrator->value);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole(RoleName::Administrator->value);
    }
}
