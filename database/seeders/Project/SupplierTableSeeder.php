<?php

namespace Database\Seeders\Project;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierTableSeeder extends Seeder
{
    public function run()
    {
        Supplier::factory()
            ->count(50)
            ->create();
    }
}
