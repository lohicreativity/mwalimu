<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleAssignmentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_assignment_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('staff_id')->default(0);
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->unsignedBigInteger('program_module_assignment_id');
            $table->unsignedBigInteger('requested_by_user_id');
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('program_module_assignment_id','prog_mod_assign_req')->references('id')->on('program_module_assignments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('requested_by_user_id')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_assignment_requests');
    }
}
