<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add loyalty columns to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('loyalty_points')->default(0)->after('is_active');
            $table->integer('total_orders_count')->default(0)->after('loyalty_points');
            $table->decimal('total_spent', 12, 2)->default(0)->after('total_orders_count');
            $table->enum('loyalty_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze')->after('total_spent');
        });

        // Create loyalty transactions log
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'expired', 'bonus', 'adjustment']);
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points', 'total_orders_count', 'total_spent', 'loyalty_tier']);
        });
    }
};
