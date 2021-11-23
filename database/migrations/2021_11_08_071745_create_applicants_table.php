<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->date('birth_date');
            $table->string('nationality');
            $table->string('gender',2);
            $table->string('email');
            $table->string('phone',20);
            $table->string('address');
            $table->string('index_number')->unique();
            $table->mediumInteger('admission_year');
            $table->string('application_number')->unique();
            $table->unsignedBigInteger('intake_id');
            $table->unsignedBigInteger('disability_status_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('intake_id')->references('id')->on('intakes')->onUpdate('cascade');
            $table->foreign('disability_status_id')->references('id')->on('disability_statuses')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('applicants');
    }
}
