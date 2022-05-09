<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecialExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('special_exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('module_assignment_id');
            $table->string('type',20)->default('FINAL');
            $table->string('postponement_letter')->nullable();
            $table->string('supporting_document')->nullable();
            $table->string('status',20)->default('PENDING');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onUpdate('cascade');
            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('special_exams');
    }
}
