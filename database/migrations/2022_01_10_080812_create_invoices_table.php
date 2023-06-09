<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->decimal('actual_amount',16,2)->nullable();
            $table->decimal('amount',16,2);
            $table->string('control_no')->nullable();
            $table->string('message')->nullable();
            $table->string('status')->nullable();
            $table->string('currency',5)->default('TZS');
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type',30);
            $table->unsignedBigInteger('applicable_id');
            $table->string('applicable_type',30);
            $table->unsignedBigInteger('fee_type_id');
            $table->unsignedBigInteger('gateway_payment_id')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
