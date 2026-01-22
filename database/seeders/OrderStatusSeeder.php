<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['status_name' => 'open', 'display_order' => 1, 'is_active' => true],
            ['status_name' => 'closed', 'display_order' => 2, 'is_active' => true],
            ['status_name' => 'cancelled', 'display_order' => 3, 'is_active' => true],
        ];

        DB::table('order_status')->insert($statuses);
    }
}
