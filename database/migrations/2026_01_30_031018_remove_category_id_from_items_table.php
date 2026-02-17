<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: category_id was never added (category system removed).
    }

    public function down(): void
    {
        // No-op
    }
};
