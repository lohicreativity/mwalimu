<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_allocation_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->double('received_amount',16,2);
            $table->timestamp('received_at');
            $table->tinyInteger('confirmation_status')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('loan_allocation_id')->references('id')->on('loan_allocations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_payments');
    }
}
