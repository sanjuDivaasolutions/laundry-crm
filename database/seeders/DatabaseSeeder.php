<?php

namespace Database\Seeders;

use Database\Seeders\Project\CompanyTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            PermissionGroupsTableSeeder::class,
            PermissionsTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,
            CompanyTableSeeder::class,
            UsersTableSeeder::class,
            RoleUserTableSeeder::class,
            CustomDatabaseSeeder::class,
        ]);
    }
}
