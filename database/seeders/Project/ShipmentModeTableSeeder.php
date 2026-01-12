<?php

namespace Database\Seeders\Project;

use App\Models\ShipmentMode;
use Illuminate\Database\Seeder;

class ShipmentModeTableSeeder extends Seeder
{
    public function run()
    {
        $data = ['Air','Ship','Transport'];

        foreach ($data as $d) {
            ShipmentMode::create(['name'=>$d]);
        }
    }
}
