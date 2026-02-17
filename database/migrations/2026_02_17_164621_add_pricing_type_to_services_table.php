<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('pricing_type', ['piece', 'weight', 'both'])->default('piece')->after('description');
            $table->decimal('price_per_pound', 8, 2)->nullable()->after('pricing_type');
            $table->decimal('minimum_weight', 8, 2)->nullable()->after('price_per_pound');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'price_per_pound', 'minimum_weight']);
        });
    }
};
