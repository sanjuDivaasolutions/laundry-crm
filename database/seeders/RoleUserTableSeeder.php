<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleUserTableSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('title', 'Admin')->where('tenant_id', 1)->first();
        $userRole = Role::where('title', 'User')->where('tenant_id', 1)->first();

        $adminUser = User::where('email', 'admin@admin.com')->first();

        if ($adminUser && $adminRole) {
            $adminUser->roles()->sync($adminRole->id);
        }

        if ($userRole) {
            $otherUsers = User::where('email', '!=', 'admin@admin.com')->get();
            foreach ($otherUsers as $user) {
                $user->roles()->sync($userRole->id);
            }
        }
    }
}
