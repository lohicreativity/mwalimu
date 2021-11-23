<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assessment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('marks');
            $table->decimal('weight');
            $table->unsignedBigInteger('module_assignment_id');
            $table->timestamps();

            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assessment_plans');
    }
}
