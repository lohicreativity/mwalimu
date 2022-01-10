<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatewayPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gateway_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('sp_code',20)->nullable();
            $table->string('pay_refId')->nullable();
            $table->string('bill_id');
            $table->string('control_no');
            $table->decimal('bill_amount',10,2);
            $table->decimal('paid_amount',10,2);
            $table->smallInteger('bill_payOpt')->nullable();
            $table->string('ccy',10)->nullable();
            $table->string('payment_channel',100);
            $table->string('cell_number',50);
            $table->string('payer_email');
            $table->string('payer_name');
            $table->string('psp_receipt_no');
            $table->string('psp_name');
            $table->string('ctry_AccNum')->nullable();
            $table->integer('flag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gateway_payments');
    }
}
