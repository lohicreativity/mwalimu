<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClearancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clearances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->string('library_status')->default('PENDING');
            $table->text('library_comment')->nullable();
            $table->string('hostel_status')->default('PENDING');
            $table->text('hostel_comment')->nullable();
            $table->string('stud_org_status')->default('PENDING');
            $table->text('stu_org_comment')->nullable();
            $table->string('finance_status')->default('PENDING');
            $table->text('finance_comment')->nullable();
            $table->string('hod_status')->default('PENDING');
            $table->text('hod_comment')->nullable();
            $table->timestamps();

            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clearances');
    }
}
