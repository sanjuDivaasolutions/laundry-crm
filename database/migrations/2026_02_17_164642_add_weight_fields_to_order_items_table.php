<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('pricing_type', ['piece', 'weight'])->default('piece')->after('service_name');
            $table->decimal('weight', 8, 2)->nullable()->after('pricing_type');
            $table->string('weight_unit', 10)->default('lb')->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'weight', 'weight_unit']);
        });
    }
};
