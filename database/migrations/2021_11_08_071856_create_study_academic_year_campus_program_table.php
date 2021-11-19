<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudyAcademicYearCampusProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_academic_year_campus_program', function (Blueprint $table) {
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('assigned_by_staff_id')->nullable();

            $table->foreign('study_academic_year_id','study_ac_year_campus_prog')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_academic_year_campus_program');
    }
}
