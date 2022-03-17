<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGraduantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('graduants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('overall_remark_id');
            $table->string('clearance_status',40)->default('PENDING');
            $table->string('status',40)->default('GRADUATING');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('overall_remark_id')->references('id')->on('overall_remarks')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('graduants');
    }
}
