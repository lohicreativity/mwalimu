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
            $table->unsignedBigInteger('payment_category_id');
            $table->unsignedBigInteger('payable_id');
            $table->string('payable_type',30);
            $table->double('amount',16,8)->default(0.00);
            $table->string('currency',10)->default('TZS');
            $table->string('reference_number');
            $table->string('control_number');
            $table->unsignedBigInteger('usable_id');
            $table->string('usable_type',30);
            $table->timestamps();

            $table->foreign('payment_category_id')->references('id')->on('payment_categories')->onUpdate('cascade');
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
