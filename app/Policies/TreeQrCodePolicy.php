<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TreeQrCode;
use App\Models\User;

class TreeQrCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageQrCodes->value);
    }

    public function view(User $user, TreeQrCode $treeQrCode): bool
    {
        return $user->can(PermissionName::ManageQrCodes->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageQrCodes->value);
    }

    public function update(User $user, TreeQrCode $treeQrCode): bool
    {
        return $user->can(PermissionName::ManageQrCodes->value);
    }

    public function delete(User $user, TreeQrCode $treeQrCode): bool
    {
        return false;
    }

    public function restore(User $user, TreeQrCode $treeQrCode): bool
    {
        return false;
    }

    public function forceDelete(User $user, TreeQrCode $treeQrCode): bool
    {
        return false;
    }
}
