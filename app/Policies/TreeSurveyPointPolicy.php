<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TreeSurveyPoint;
use App\Models\User;

class TreeSurveyPointPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function view(User $user, TreeSurveyPoint $treeSurveyPoint): bool
    {
        return $user->can(PermissionName::ViewTrees->value);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, TreeSurveyPoint $treeSurveyPoint): bool
    {
        return $treeSurveyPoint->registered_tree_id === null
            && $user->can(PermissionName::ManageVerifications->value);
    }

    public function delete(User $user, TreeSurveyPoint $treeSurveyPoint): bool
    {
        return false;
    }
}
