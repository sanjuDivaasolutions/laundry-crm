<?php

/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 04/12/25, 11:10 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

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
        // Create pos_sessions table
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_number')->unique();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();
            $table->decimal('expected_cash', 15, 2)->nullable();
            $table->decimal('cash_difference', 15, 2)->nullable();
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_cash_sales', 15, 2)->default(0);
            $table->decimal('total_card_sales', 15, 2)->default(0);
            $table->decimal('total_other_sales', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index for quick lookups
            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
        });

        // Add pos_session_id to sales_invoices table
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('pos_session_id')
                ->nullable()
                ->after('warehouse_id')
                ->constrained('pos_sessions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign key from sales_invoices first
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn('pos_session_id');
        });

        // Drop pos_sessions table
        Schema::dropIfExists('pos_sessions');
    }
};
