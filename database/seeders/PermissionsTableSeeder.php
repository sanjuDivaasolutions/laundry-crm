<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Services\PermissionDataService;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        $data = PermissionDataService::getData();

        foreach ($data as $p) {
            Permission::firstOrCreate(
                ['title' => $p['permission']],
                ['permission_group_id' => $p['group_id'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
