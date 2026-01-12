<?php

namespace Database\Seeders\Project;

use App\Models\Category;
use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureTableSeeder extends Seeder
{
    public function run()
    {
        $data = ['Abidos Range','Adhesive','Bulevar Range','Dreire Range','Luxury Mosaics','Job Lot','Pireo Range','Self Levelling'];

        foreach ($data as $d) {
            Feature::create(['name'=>$d,'active'=>1]);
        }
    }
}
