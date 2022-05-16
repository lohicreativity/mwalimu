<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_results', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name');
            $table->string('subject_code');
            $table->string('grade',4);
            $table->unsignedBigInteger('out_result_detail_id');
            $table->timestamps();

            $table->foreign('out_result_detail_id')->references('id')->on('out_results_details')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('out_results');
    }
}
