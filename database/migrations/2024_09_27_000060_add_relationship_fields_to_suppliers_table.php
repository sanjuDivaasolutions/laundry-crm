<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToSuppliersTable extends Migration
{
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id', 'company_fk_suppliers')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9531076')->references('id')->on('payment_terms');
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->foreign('billing_address_id', 'billing_address_fk_9531077')->references('id')->on('contact_addresses');
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->foreign('shipping_address_id', 'shipping_address_fk_9531078')->references('id')->on('contact_addresses');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id', 'currency_fk_9531080')->references('id')->on('currencies');
        });
    }

    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign('company_fk_suppliers');
            $table->dropForeign('payment_term_fk_9531076');
            $table->dropForeign('billing_address_fk_9531077');
            $table->dropForeign('shipping_address_fk_9531078');
            $table->dropForeign('currency_fk_9531080');
            $table->dropColumn(['company_id', 'payment_term_id', 'billing_address_id', 'shipping_address_id', 'currency_id']);
        });
    }
}
