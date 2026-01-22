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
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            $table->enum('status_type', array_column(\App\Enums\OrderStatusTypeEnum::cases(), 'value'));
            
            // These reference IDs from processing_status or order_status 
            // tables depending on status_type.
            $table->unsignedBigInteger('old_status_id')->nullable();
            $table->unsignedBigInteger('new_status_id')->nullable();
            
            $table->unsignedBigInteger('changed_by_employee_id');
            $table->text('remarks')->nullable();
            $table->timestamp('changed_at')->index();
            
            $table->index(['order_id', 'changed_at'], 'idx_order_history');
            $table->index(['changed_by_employee_id', 'changed_at'], 'idx_employee_changes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
