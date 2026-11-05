<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            RoleName::Administrator->value => PermissionName::cases(),
            RoleName::CertifiedForester->value => [
                PermissionName::AccessPanel, PermissionName::ViewDashboard, PermissionName::ViewTrees,
                PermissionName::UpdateTrees, PermissionName::ViewMonitoring, PermissionName::CreateMonitoring,
                PermissionName::VerifyMonitoring, PermissionName::UploadPhotos, PermissionName::ManageSpecies,
                PermissionName::ManageVerifications, PermissionName::ViewReports,
            ],
            RoleName::MaintenancePersonnel->value => [
                PermissionName::AccessPanel, PermissionName::ViewDashboard, PermissionName::ViewTrees,
                PermissionName::ViewMonitoring, PermissionName::CreateMonitoring, PermissionName::UploadPhotos,
            ],
            RoleName::AuthorizedStaff->value => [
                PermissionName::AccessPanel, PermissionName::ViewDashboard, PermissionName::ViewTrees,
                PermissionName::CreateTrees, PermissionName::UpdateTrees, PermissionName::ViewMonitoring,
                PermissionName::CreateMonitoring, PermissionName::UploadPhotos, PermissionName::ManageCampusAreas,
                PermissionName::ManageQrCodes, PermissionName::ViewReports,
            ],
            RoleName::DataEncoder->value => [
                PermissionName::AccessPanel, PermissionName::ViewDashboard, PermissionName::ViewTrees,
                PermissionName::CreateTrees, PermissionName::UpdateTrees, PermissionName::ViewMonitoring,
                PermissionName::CreateMonitoring, PermissionName::UploadPhotos,
            ],
            RoleName::User->value => [PermissionName::ViewTrees],
        ];

        foreach ($roles as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions(
                array_map(fn (PermissionName $permission) => $permission->value, $permissions),
            );
        }

        User::where('email', 'admin@test.com')->first()?->syncRoles([RoleName::Administrator->value]);
        User::where('email', 'user@test.com')->first()?->syncRoles([RoleName::User->value]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
