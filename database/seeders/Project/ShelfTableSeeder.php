<?php

namespace Database\Seeders\Project;

use App\Models\Shelf;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ShelfTableSeeder extends Seeder
{
    public function run()
    {
         $data = [
            '20FT',
            '40FT',
            '40FT HQ',
            '45FT',
            'LCL',
            'Open',
        ];

        foreach ($data as $d) {
            Shelf::query()->create(['name'=>$d,'active'=>1,'warehouse_id'=>1]);
        }
    }
}
