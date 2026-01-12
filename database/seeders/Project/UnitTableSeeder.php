<?php

namespace Database\Seeders\Project;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    public function run()
    {
        $data = ['Each','Per Meter','Per Sheet','Per Piece','Per Bag','Job Lot','Per Set','Per Mat','Per Board','Per Trim','Per Lube','Per Tube','Per Box'];

        foreach ($data as $d) {
            Unit::create(['name'=>$d,'active'=>1]);
        }
    }
}
