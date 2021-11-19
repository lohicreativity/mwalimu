<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->date('birth_date');
            $table->string('nationality');
            $table->string('gender',2);
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->unsignedBigInteger('applicant_id');
            $table->string('registration_number')->unique();
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('studentship_status_id');
            $table->unsignedBigInteger('academic_status_id');
            $table->unsignedBigInteger('disability_status_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('insurance_id');
            $table->mediumInteger('year_of_study');
            $table->mediumInteger('registration_year');
            $table->string('entry_mode');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('academic_status_id')->references('id')->on('academic_statuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('studentship_status_id')->references('id')->on('studentship_statuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('disability_status_id')->references('id')->on('disability_statuses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onUpdate('cascade')->onDelete('cascade');
             $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
