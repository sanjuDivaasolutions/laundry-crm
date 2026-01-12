<?php

namespace Database\Seeders\Project;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    public function run()
    {
        $data = ['Abidos Range','Adhesive','Bulevar Range','Dreire Range','Luxury Mosaics','Job Lot','Pireo Range','Self Levelling'];

        foreach ($data as $d) {
            Category::create(['name'=>$d,'active'=>1]);
        }
    }
}
