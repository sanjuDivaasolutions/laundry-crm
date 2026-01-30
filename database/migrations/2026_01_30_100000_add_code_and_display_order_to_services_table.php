<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('code', 50)->unique()->nullable()->after('tenant_id');
            $table->integer('display_order')->default(0)->after('description');

            // Add index for ordering
            $table->index(['tenant_id', 'display_order', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'display_order', 'name']);
            $table->dropColumn(['code', 'display_order']);
        });
    }
};
