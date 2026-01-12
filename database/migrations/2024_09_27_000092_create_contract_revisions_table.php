<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractRevisionsTable extends Migration
{
    public function up()
    {
        Schema::create('contract_revisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('contract_type');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('installment_count')->nullable();
            $table->boolean('limited_installment')->default(0);
            $table->float('sub_total', 8, 2);
            $table->float('tax_total', 8, 2);
            $table->float('tax_rate', 5, 2);
            $table->float('grand_total', 8, 2);
            $table->boolean('active')->default(0);
            $table->string('stripe_product')->nullable();
            $table->string('stripe_product_price')->nullable();
            $table->string('stripe_subscription_meta')->nullable();
            $table->string('stripe_subscription')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_revisions');
    }
}
