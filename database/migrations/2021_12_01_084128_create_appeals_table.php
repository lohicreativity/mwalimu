<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('module_assignment_id');
            $table->unsignedBigInteger('examination_result_id');
            $table->unsignedBigInteger('payment_id');
            $table->decimal('old_score',8,1);
            $table->decimal('new_score',8,1)->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('examination_result_id')->references('id')->on('examination_results')->onUpdate('cascade');
            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
            $table->foreign('payment_id')->references('id')->on('payments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appeals');
    }
}
