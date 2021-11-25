<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExaminationPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examination_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nta_level_id');
            $table->decimal('course_work_min_mark',10,2);
            $table->decimal('course_work_percentage_pass',10,2);
            $table->decimal('course_work_pass_score',10,2);
            $table->decimal('final_min_mark',10,2);
            $table->decimal('final_percentage_pass',10,2);
            $table->decimal('final_pass_score',10,2);
            $table->decimal('module_pass_mark',10,2);
            $table->string('type',50);
            $table->timestamps();

            $table->foreign('nta_level_id')->references('id')->on('nta_levels')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('examination_policies');
    }
}
