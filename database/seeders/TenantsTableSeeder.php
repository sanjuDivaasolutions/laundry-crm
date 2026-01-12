<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = [
            [
                'id' => 1,
                'name' => 'Default Tenant',
                'domain' => 'localhost', // Default for local dev
                'active' => true,
                'settings' => ['locale' => 'en'],
            ],
            [
                'id' => 2,
                'name' => 'Demo Tenant',
                'domain' => 'demo.localhost',
                'active' => true,
                'settings' => ['locale' => 'en'],
            ]
        ];

        foreach ($tenants as $tenant) {
            Tenant::updateOrCreate(['id' => $tenant['id']], $tenant);
        }
    }
}
