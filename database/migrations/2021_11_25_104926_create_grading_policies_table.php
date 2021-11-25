<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingPoliciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grading_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nta_level_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->decimal('min_score',10,2);
            $table->decimal('max_score',10,2);
            $table->string('grade',2);
            $table->mediumInteger('point');
            $table->string('remark');
            $table->timestamps();

            $table->foreign('nta_level_id')->references('id')->on('nta_levels')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grading_policies');
    }
}
