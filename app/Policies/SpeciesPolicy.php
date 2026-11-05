<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Species;
use App\Models\User;

class SpeciesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function view(User $user, Species $species): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageSpecies->value);
    }

    public function update(User $user, Species $species): bool
    {
        return $user->can(PermissionName::ManageSpecies->value);
    }

    public function delete(User $user, Species $species): bool
    {
        return $user->can(PermissionName::ManageSpecies->value) && ! $species->trees()->exists();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionName::ManageSpecies->value);
    }
}
