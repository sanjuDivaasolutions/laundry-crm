<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('processing_status')->insert([
            'status_name' => 'Cancelled',
            'display_order' => 6,
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('processing_status')->where('status_name', 'Cancelled')->delete();
    }
};
