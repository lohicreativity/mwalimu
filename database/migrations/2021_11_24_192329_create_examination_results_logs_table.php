<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExaminationResultsLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examination_results_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('module_assignment_id');
            $table->decimal('course_work_score',8,1)->nullable();
            $table->decimal('final_score',8,1)->nullable();
            $table->decimal('supp_score',8,1)->nullable();
            $table->string('supp_remark',20)->nullable();
            $table->decimal('appeal_score',8,1)->nullable();
            $table->decimal('appeal_supp_score',8,1)->nullable();
            $table->decimal('total_score',8,1)->nullable();
            $table->string('exam_type')->default('FINAL');
            $table->string('exam_category')->default('FIRST');
            $table->unsignedBigInteger('retakable_id')->nullable();
            $table->string('retakable_type',20)->nullable();
            $table->string('grade',10)->nullable();
            $table->integer('point')->nullable();
            $table->string('course_work_remark',20)->nullable();
            $table->string('final_remark',20)->nullable();
            $table->string('final_exam_remark',20)->nullable();
            $table->timestamp('final_uploaded_at')->nullable();
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->unsignedBigInteger('processed_by_user_id')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('final_processed_by_user_id')->default(0);

            $table->timestamp('final_processed_at')->nullable();
            $table->timestamp('supp_processed_at')->nullable();
            $table->unsignedBigInteger('supp_processed_by_user_id')->default(0);
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
        Schema::dropIfExists('examination_results_logs');
    }
}
