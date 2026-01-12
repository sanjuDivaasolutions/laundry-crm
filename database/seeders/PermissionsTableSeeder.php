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

        $permissions = [];
        $count = 1;
        foreach ($data as $p) {
            $permissions[] = [
                'id' => $count,
                'title' => $p['permission'],
                'permission_group_id' => $p['group_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $count++;
        }

        Permission::insert($permissions);
    }
}
