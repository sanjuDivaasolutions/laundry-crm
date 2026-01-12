<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelationshipFieldsToBuyersTable extends Migration
{
    public function up()
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id', 'company_fk_buyers')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id', 'currency_fk_9531138')->references('id')->on('currencies');
            $table->unsignedBigInteger('payment_term_id')->nullable();
            $table->foreign('payment_term_id', 'payment_term_fk_9531139')->references('id')->on('payment_terms');
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->foreign('billing_address_id', 'billing_address_fk_9531140')->references('id')->on('contact_addresses');
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->foreign('shipping_address_id', 'shipping_address_fk_9531141')->references('id')->on('contact_addresses');
        });
    }

    public function down()
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropForeign('company_fk_buyers');
            $table->dropForeign('currency_fk_9531138');
            $table->dropForeign('payment_term_fk_9531139');
            $table->dropForeign('billing_address_fk_9531140');
            $table->dropForeign('shipping_address_fk_9531141');
            $table->dropColumn(['company_id', 'currency_id', 'payment_term_id', 'billing_address_id', 'shipping_address_id']);
        });
    }
}
