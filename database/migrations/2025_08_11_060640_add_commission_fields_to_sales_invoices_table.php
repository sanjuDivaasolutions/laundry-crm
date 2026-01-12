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
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->decimal('commission', 5, 2)->nullable()->default(0.00);
            $table->decimal('commission_total', 10, 2)->nullable()->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn(['commission', 'commission_total']);
        });
    }
};
