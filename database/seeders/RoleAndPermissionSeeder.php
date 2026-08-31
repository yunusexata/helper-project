<?php

namespace Database\Seeders;

use App\Helpers\PermissionHelper;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Generate permissions based on PermissionHelper
        foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
            foreach ($types as $type) {
                $permissionName = PermissionHelper::transform($access, $type);
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // 2. Define roles and assign dot-notation permissions

        // Super Admin: All permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->syncPermissions(Permission::all());

        // Admin: All permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        // Staff: Read on whitelists/routines, CRUD on histories/attachments/requests
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $staffRole->syncPermissions([
            PermissionHelper::transform(PermissionHelper::ACCESS_EMPLOYEE_WHITELIST, PermissionHelper::TYPE_READ),

            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_READ),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_UPDATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_DELETE),

            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_READ),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_UPDATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_DELETE),

            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_READ),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_UPDATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_DELETE),
        ]);

        // Helper: Read routines, view/create requests, CRUD on histories (except delete)
        $helperRole = Role::firstOrCreate(['name' => 'Helper']);
        $helperRole->syncPermissions([
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_REQUEST, PermissionHelper::TYPE_READ),

            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_READ),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY, PermissionHelper::TYPE_UPDATE),

            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_CREATE),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_READ),
            PermissionHelper::transform(PermissionHelper::ACCESS_HELPER_JOBDESK_DAILY_HISTORY_ATTACHMENT, PermissionHelper::TYPE_DELETE),
        ]);
    }
}
