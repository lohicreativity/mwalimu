<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stream_components', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year_of_study');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->mediumInteger('number_of_students');
            $table->mediumInteger('number_of_streams');
            $table->unsignedBigInteger('assigned_by_staff_id')->nullable();
            $table->timestamps();

            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stream_components');
    }
}
