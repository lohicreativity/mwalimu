<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExaminationResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examination_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('module_assignment_id');
            $table->decimal('course_work_score')->default(0.00);
            $table->decimal('final_score')->default(0.00);
            $table->decimal('total_score')->default(0.00);
            $table->string('exam_type')->default('FINAL');
            $table->string('grade',10)->nullable();
            $table->string('course_work_remark',20)->nullable();
            $table->string('final_remark',20)->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->unsignedBigInteger('processed_by_user_id')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('final_processed_by_user_id')->default(0);
            $table->timestamp('final_processed_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onUpdate('cascade');
            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
            $table->foreign('uploaded_by_user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('examination_results');
    }
}
