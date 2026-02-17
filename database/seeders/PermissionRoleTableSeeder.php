<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionRoleTableSeeder extends Seeder
{
    public function run()
    {
        $admin_permissions = Permission::all();

        $adminRole = Role::where('title', 'Admin')->where('tenant_id', 1)->first();
        $userRole = Role::where('title', 'User')->where('tenant_id', 1)->first();

        if ($adminRole) {
            $adminRole->permissions()->sync($admin_permissions->pluck('id'));
        }

        if ($userRole) {
            $user_permissions = $admin_permissions->filter(function ($permission) {
                return substr($permission->title, 0, 5) != 'user_' && substr($permission->title, 0, 5) != 'role_' && substr($permission->title, 0, 11) != 'permission_';
            });
            $userRole->permissions()->sync($user_permissions);
        }
    }
}
