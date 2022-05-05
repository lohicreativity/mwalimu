<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostponementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postponements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id')->default(0);
            $table->string('category'); // SEMESTER or YEAR
            $table->string('letter');
            $table->string('supporting_document');
            $table->text('recommendation')->nullable();
            $table->unsignedBigInteger('recommended_by_user_id')->nullable();
            $table->string('status',20)->default('PENDING');
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
        Schema::dropIfExists('postponements');
    }
}
