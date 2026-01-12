<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('buyer_id')->constrained('agents')->onDelete('set null');
            $table->index(['agent_id']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('buyer_id')->constrained('agents')->onDelete('set null');
            $table->index(['agent_id']);
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->after('company_id')->constrained('agents')->onDelete('set null');
            $table->decimal('commission_rate', 8, 2)->nullable()->after('agent_id');
            $table->string('agent_name')->nullable()->after('commission_rate');
            $table->index(['agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn('agent_id');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn('agent_id');
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropIndex(['agent_id']);
            $table->dropColumn(['agent_id', 'commission_rate', 'agent_name']);
        });
    }
};