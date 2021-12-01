<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsPublicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results_publications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('semester_id')->default(0);
            $table->string('status',20)->default('UNPUBLISHED');
            $table->string('type',20)->default('FINAL');
            $table->unsignedBigInteger('published_by_user_id');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('published_by_user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('results_publications');
    }
}
