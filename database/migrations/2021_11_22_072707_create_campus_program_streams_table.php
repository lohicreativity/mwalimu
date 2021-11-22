<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampusProgramStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campus_program_streams', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year_of_study');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->smallInteger('number');
            $table->unsignedBigInteger('assigned_by_staff_id');
            $table->timestamps();

            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('assigned_by_staff_id')->references('id')->on('staffs')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campus_program_streams');
    }
}
