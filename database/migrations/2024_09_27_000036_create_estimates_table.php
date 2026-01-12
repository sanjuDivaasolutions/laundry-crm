<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatesTable extends Migration
{
    public function up()
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('quo_number');
            $table->string('reference_no')->nullable();
            $table->string('type');
            $table->date('date')->nullable();
            $table->date('estimated_shipment_date')->nullable();
            $table->string('remarks')->nullable();
            $table->float('sub_total', 10, 2);
            $table->float('tax_total', 10, 2);
            $table->float('tax_rate', 10, 2);
            $table->float('grand_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('estimates');
    }
}
