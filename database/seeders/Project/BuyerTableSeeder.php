<?php

namespace Database\Seeders\Project;

use App\Models\Buyer;
use Illuminate\Database\Seeder;

class BuyerTableSeeder extends Seeder
{
    public function run()
    {
        Buyer::factory()
            ->count(50)
            ->create();
    }
}
