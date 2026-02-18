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
        // Fix payments.order_id: add cascade delete
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        // Make employee columns nullable (required for nullOnDelete) and add FK constraints
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_employee_id')->nullable()->change();
            $table->foreign('created_by_employee_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('received_by_employee_id')->nullable()->change();
            $table->foreign('received_by_employee_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('delivery_schedules', function (Blueprint $table) {
            $table->foreign('assigned_to_employee_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('order_status_history', function (Blueprint $table) {
            $table->unsignedBigInteger('changed_by_employee_id')->nullable()->change();
            $table->foreign('changed_by_employee_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropForeign(['changed_by_employee_id']);
            $table->unsignedBigInteger('changed_by_employee_id')->nullable(false)->change();
        });

        Schema::table('delivery_schedules', function (Blueprint $table) {
            $table->dropForeign(['assigned_to_employee_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['received_by_employee_id']);
            $table->unsignedBigInteger('received_by_employee_id')->nullable(false)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['created_by_employee_id']);
            $table->unsignedBigInteger('created_by_employee_id')->nullable(false)->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }
};
