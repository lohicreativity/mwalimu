<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAllocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('index_number');
            $table->string('registration_number');
            $table->string('name');
            $table->string('sex');
            $table->smallInteger('year_of_study');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->double('meals_and_accomodation',16,2);
            $table->double('books_and_stationeries',16,2);
            $table->double('tuition_fee',16,2);
            $table->double('field_training',16,2);
            $table->double('research',16,2);
            $table->double('loan_amount',16,2);
            $table->double('loan_difference',16,2)->default(0.00);
            $table->unsignedBigInteger('student_id')->nullable();
            $table->tinyInteger('has_signed')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_allocations');
    }
}
