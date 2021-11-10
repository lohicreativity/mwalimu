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
        Schema::create('program_module_assigments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->string('category',20)->default('CORE');
            $table->timestamps();

            $table->foreign('study_academic_year_id','study_ac_yr_prog_mod_assign')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_module_assigments');
    }
}
