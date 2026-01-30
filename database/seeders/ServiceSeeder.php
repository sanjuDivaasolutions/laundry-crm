<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1; // Default tenant

        $services = [
            ['tenant_id' => $tenantId, 'name' => 'Wash & Fold', 'description' => 'Standard washing and folding service', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => $tenantId, 'name' => 'Dry Cleaning', 'description' => 'Professional dry cleaning service', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => $tenantId, 'name' => 'Ironing', 'description' => 'Press and iron service', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['tenant_id' => $tenantId, 'name' => 'Stain Removal', 'description' => 'Specialized stain removal treatment', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('services')->insert($services);
    }
}
