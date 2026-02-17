<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers');
            $table->enum('type', ['pickup', 'delivery'])->index();
            $table->date('scheduled_date')->index();
            $table->time('scheduled_time')->nullable();
            $table->string('address', 500)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_to_employee_id')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_transit', 'completed', 'cancelled'])->default('pending')->index();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
