<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Services\PermissionDataService;
use Illuminate\Database\Seeder;

class PermissionGroupsTableSeeder extends Seeder
{
    public function run()
    {
        $data = PermissionDataService::getGroupData();

        foreach ($data as $g) {
            PermissionGroup::firstOrCreate(
                ['name' => $g],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
