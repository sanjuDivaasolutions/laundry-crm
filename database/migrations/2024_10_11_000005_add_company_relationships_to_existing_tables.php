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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index(['company_id']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index(['company_id']);
            }
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_invoices', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index(['company_id']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index(['company_id']);
            }
        });

        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasColumn('buyers', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                $table->index(['company_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            }
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('sales_invoices', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            }
        });

        Schema::table('buyers', function (Blueprint $table) {
            if (Schema::hasColumn('buyers', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropIndex(['company_id']);
                $table->dropColumn('company_id');
            }
        });
    }
};