<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSemesterRemarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('semester_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id');
            $table->mediumInteger('year_of_study');
            $table->decimal('gpa',8,4);
            $table->string('remark',20);
            $table->text('serialized')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('semester_remarks');
    }
}
