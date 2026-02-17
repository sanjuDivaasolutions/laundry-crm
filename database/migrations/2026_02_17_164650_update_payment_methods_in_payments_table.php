<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing 'upi' records to 'other'
        DB::table('payments')->where('payment_method', 'upi')->update(['payment_method' => 'other']);

        // Update the enum column
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','card','apple_pay','google_pay','other') NOT NULL DEFAULT 'cash'");
        }
        // SQLite doesn't enforce enum constraints, so no schema change needed
    }

    public function down(): void
    {
        // Migrate apple_pay/google_pay back to other
        DB::table('payments')->whereIn('payment_method', ['apple_pay', 'google_pay'])->update(['payment_method' => 'other']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('cash','card','upi','other') NOT NULL DEFAULT 'cash'");
        }
    }
};
