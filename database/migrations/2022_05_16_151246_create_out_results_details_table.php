<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutResultsDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('out_results_details', function (Blueprint $table) {
            $table->id();
            $table->string('reg_no');
            $table->string('index_number');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->string('gender',2);
            $table->string('birth_date',20);
            $table->string('academic_year');
            $table->decimal('gpa',10,2);
            $table->string('classification');
            $table->unsignedBigInteger('applicant_id');
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('out_results_details');
    }
}
