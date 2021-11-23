<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElectiveModuleLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elective_module_limits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->date('deadline');
            $table->timestamps();

            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onUpdate('cascade');
            $table->foreign('study_academic_year_id','study_ac_yr_el_mod_lim')->references('id')->on('study_academic_years')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elective_module_limits');
    }
}
