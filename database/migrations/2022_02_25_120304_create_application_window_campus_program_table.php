<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationWindowCampusProgramTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_window_campus_program', function (Blueprint $table) {
            $table->unsignedBigInteger('application_window_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('assigned_by_user_id')->nullable();

            $table->foreign('application_window_id','app_window_campus_prog')->references('id')->on('application_windows')->onUpdate('cascade');
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
        Schema::dropIfExists('application_window_campus_program');
    }
}
