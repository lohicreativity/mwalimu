<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExaminationProcessRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examination_process_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id')->default(0);
            $table->smallInteger('year_of_study');
            $table->timestamps();

            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('examination_process_records');
    }
}
