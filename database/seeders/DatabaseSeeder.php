<?php

namespace Database\Seeders;

use Database\Seeders\Custom\LanguageTableSeeder;
use Database\Seeders\Custom\LanguageTermGroupTableSeeder;
use Database\Seeders\Custom\LanguageTermTableSeeder;
use Database\Seeders\Custom\LanguageTranslationTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            TenantsTableSeeder::class,
            PermissionGroupsTableSeeder::class,
            PermissionsTableSeeder::class,
            LanguageTableSeeder::class,
            LanguageTermGroupTableSeeder::class,
            LanguageTermTableSeeder::class,
            LanguageTranslationTableSeeder::class,
            RolesTableSeeder::class,
            PermissionRoleTableSeeder::class,
            UsersTableSeeder::class,
            CurrenciesTableSeeder::class,
            RoleUserTableSeeder::class,
            ProcessingStatusSeeder::class,
            OrderStatusSeeder::class,
            CategorySeeder::class,
            ServiceSeeder::class,
            ItemSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
