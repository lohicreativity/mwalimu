<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantsSubmissionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicants_submission_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('program_level_id');
            $table->tinyInteger('submitted')->default(0);
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('program_level_id')->references('id')->on('awards')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicants_submission_logs');
    }
}
