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

        $groups = [];
        $count = 1;
        foreach ($data as $g) {
            $groups[] = [
                'id'=>$count,
                'name'=>$g,
                'created_at'=>now(),
                'updated_at'=>now(),
            ];
            $count++;
        }

        PermissionGroup::insert($groups);
    }
}
