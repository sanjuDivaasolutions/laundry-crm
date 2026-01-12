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
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode', 50)->nullable()->unique()->after('sku');
            $table->string('barcode_type', 20)->default('code128')->after('barcode');
            $table->index('barcode', 'idx_products_barcode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_barcode');
            $table->dropColumn(['barcode', 'barcode_type']);
        });
    }
};
