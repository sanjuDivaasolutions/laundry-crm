<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessingStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['status_name' => 'received', 'display_order' => 1, 'is_active' => true],
            ['status_name' => 'washing', 'display_order' => 2, 'is_active' => true],
            ['status_name' => 'drying', 'display_order' => 3, 'is_active' => true],
            ['status_name' => 'ready_for_pickup', 'display_order' => 4, 'is_active' => true],
        ];

        DB::table('processing_status')->insert($statuses);
    }
}
