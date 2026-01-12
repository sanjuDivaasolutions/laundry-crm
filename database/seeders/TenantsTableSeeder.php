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
        Tenant::create([
            'id' => 1,
            'name' => 'Default Tenant',
            'domain' => 'default',
            'active' => true,
            'settings' => ['locale' => 'en'],
        ]);
    }
}
