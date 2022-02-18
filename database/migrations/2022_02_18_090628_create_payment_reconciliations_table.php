<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentReconciliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('SpReconcReqId');
            $table->string('ReconcStsCode');
            $table->string('SpBillId');
            $table->string('BillCtrNum');
            $table->string('pspTrxId');
            $table->string('PaidAmt');
            $table->string('CCy');
            $table->string('PayRefId');
            $table->string('TrxDtTm');
            $table->string('CtrAccNum');
            $table->string('UsdPayChnl');
            $table->string('PspName');
            $table->string('PspCode');
            $table->string('DptCellNum');
            $table->string('DptName');
            $table->string('DptEmailAddr');
            $table->string('Remarks');
            $table->string('ReconcRsv1');
            $table->string('ReconcRsv2')->nullable();
            $table->string('ReconcRsv3')->nullable();
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
        Schema::dropIfExists('payment_reconciliations');
    }
}
