<?php

use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('payment_number', 20)->unique();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('customer_id')->constrained('customers');
            $table->dateTime('payment_date')->index();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', array_column(PaymentMethodEnum::cases(), 'value'))->default(PaymentMethodEnum::Cash->value);
            $table->string('transaction_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by_employee_id');
            $table->timestamp('created_at');

            $table->index(['order_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
