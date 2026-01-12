<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'id'             => 1,
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'active'         => 1,
                'company_id'     => 1,
                'remember_token' => null,
                'language_id'    => null,//config('system.defaults.language.id',1),
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];
        User::insert($users);

        /*User::factory()
            ->count(50)
            ->create();*/
    }
}
