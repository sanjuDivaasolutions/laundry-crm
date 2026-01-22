<?php

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('order_number', 20)->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('order_date')->index();
            $table->date('promised_date');

            $table->integer('total_items');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);

            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2);
            $table->enum('payment_status', array_column(PaymentStatusEnum::cases(), 'value'))->default(PaymentStatusEnum::Unpaid->value);

            $table->foreignId('processing_status_id')->constrained('processing_status');
            $table->foreignId('order_status_id')->constrained('order_status');

            $table->dateTime('actual_ready_date')->nullable();
            $table->dateTime('picked_up_at')->nullable();

            $table->text('notes')->nullable();
            $table->boolean('urgent')->default(false);

            $table->unsignedBigInteger('created_by_employee_id');

            $table->timestamps();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();

            $table->index(['customer_id', 'order_date']);
            $table->index(['processing_status_id', 'order_status_id']);
            $table->index(['order_status_id', 'promised_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
