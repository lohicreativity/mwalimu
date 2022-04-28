<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campus_program_id');
            $table->double('amount_in_tzs',16,2);
            $table->double('amount_in_usd',16,2);
            $table->unsignedBigInteger('fee_item_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->string('status');
            $table->timestamps();

            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade');
            $table->foreign('fee_item_id')->references('id')->on('fee_items')->onUpdate('cascade');
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
        Schema::dropIfExists('program_fees');
    }
}
