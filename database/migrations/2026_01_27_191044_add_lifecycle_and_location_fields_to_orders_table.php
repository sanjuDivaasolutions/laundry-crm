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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('hanger_number', 50)->nullable()->after('urgent');
            $table->decimal('tax_rate', 5, 2)->default(0.00)->after('total_amount');
            $table->decimal('tax_amount', 10, 2)->default(0.00)->after('tax_rate');
            $table->string('discount_type', 20)->nullable()->after('discount_amount'); // percentage or fixed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
