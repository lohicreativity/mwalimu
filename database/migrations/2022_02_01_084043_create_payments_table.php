<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fee_type_id');
            $table->double('amount',16,8)->default(0.00);
            $table->string('currency',10)->default('TZS');
            $table->string('reference_number');
            $table->string('invoice_number');
            $table->string('receipt_number');
            $table->string('control_number');
            $table->unsignedBigInteger('usable_id');
            $table->string('usable_type',30);
            $table->timestamps();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
