<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGpaClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gpa_classifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nta_level_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->decimal('min_gpa',10,1);
            $table->decimal('max_gpa',10,1);
            $table->string('name');
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
        Schema::dropIfExists('gpa_classifications');
    }
}
