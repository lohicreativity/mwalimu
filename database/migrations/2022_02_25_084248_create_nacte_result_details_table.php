<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNacteResultDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nacte_result_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->string('institution');
            $table->string('programme');
            $table->string('firstname');
            $table->string('middlename');
            $table->string('surname');
            $table->string('avn',20);
            $table->string('gender',10);
            $table->string('diploma_gpa',4);
            $table->string('diploma_code',50);
            $table->string('diploma_category');
            $table->string('diploma_graduation_year',10);
            $table->string('username');
            $table->string('registration_number',50);
            $table->string('date_birth');
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
        Schema::dropIfExists('nacte_result_details');
    }
}
