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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('item_id')->constrained()->nullOnDelete();
            $table->string('color', 50)->nullable()->after('service_name');
            $table->string('brand', 50)->nullable()->after('color');
            $table->text('defect_notes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
