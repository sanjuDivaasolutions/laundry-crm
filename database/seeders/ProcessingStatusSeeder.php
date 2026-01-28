<?php

namespace Database\Seeders;

use App\Enums\ProcessingStatusEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessingStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProcessingStatusEnum::cases() as $index => $status) {
            DB::table('processing_status')->updateOrInsert(
                ['status_name' => $status->value],
                ['display_order' => $index + 1, 'is_active' => true]
            );
        }
    }
}
