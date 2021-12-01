<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carry_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('module_assignment_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('examination_result_id');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('examination_result_id')->references('id')->on('examination_results')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carry_histories');
    }
}
