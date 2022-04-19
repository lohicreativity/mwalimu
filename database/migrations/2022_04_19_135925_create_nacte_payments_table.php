<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNactePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nacte_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->double('amount',16,2);
            $table->double('balance',16,2);
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->timestamps();

            $table->foreign('campus_id')->references('id')->on('campuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nacte_payments');
    }
}
