<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'active' => 1,
                'company_id' => null,
                'tenant_id' => 1,
                'remember_token' => null,
                'language_id' => null,
            ]
        );
    }
}
