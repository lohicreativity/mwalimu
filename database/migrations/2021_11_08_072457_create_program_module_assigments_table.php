<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramModuleAssigmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_module_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->mediumInteger('year_of_study');
            $table->string('category',20)->default('COMPULSORY');
            $table->string('type',20)->default('CORE');
            $table->timestamps();

            $table->foreign('study_academic_year_id','study_ac_yr_prog_mod_assign')->references('id')->on('study_academic_years')->onUpdate('cascade');

            $table->foreign('semester_id')->references('id')->on('semesters')->onUpdate('cascade');
            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade');
            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_module_assignments');
    }
}
