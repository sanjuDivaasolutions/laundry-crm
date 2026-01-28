<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (OrderStatusEnum::cases() as $index => $status) {
            DB::table('order_status')->updateOrInsert(
                ['status_name' => $status->value],
                ['display_order' => $index + 1, 'is_active' => true]
            );
        }
    }
}
