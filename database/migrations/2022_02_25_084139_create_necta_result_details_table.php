<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNectaResultDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('necta_result_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('center_name');
            $table->string('center_number');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('index_number',20);
            $table->string('sex',10);
            $table->string('division',4);
            $table->tinyInteger('points');
            $table->tinyInteger('exam_id');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('necta_result_details');
    }
}
