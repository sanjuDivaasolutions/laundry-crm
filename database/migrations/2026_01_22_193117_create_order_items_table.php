<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('item_name', 100);
            $table->string('service_name', 100);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_price', 10, 2);
            $table->string('barcode', 50)->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
