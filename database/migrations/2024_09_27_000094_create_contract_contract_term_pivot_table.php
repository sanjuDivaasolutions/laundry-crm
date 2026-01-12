<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractContractTermPivotTable extends Migration
{
    public function up()
    {
        Schema::create('contract_contract_term', function (Blueprint $table) {
            $table->unsignedBigInteger('contract_id');
            $table->foreign('contract_id', 'contract_id_fk_9918834')->references('id')->on('contracts')->onDelete('cascade');
            $table->unsignedBigInteger('contract_term_id');
            $table->foreign('contract_term_id', 'contract_term_id_fk_9918834')->references('id')->on('contract_terms')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_contract_term');
    }
}
