<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(
            ['title' => 'Admin', 'tenant_id' => 1],
            ['created_at' => now(), 'updated_at' => now()]
        );

        Role::firstOrCreate(
            ['title' => 'User', 'tenant_id' => 1],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }
}
