<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->unsignedBigInteger('program_module_assignment_id');
            $table->unsignedBigInteger('assigned_by_staff_id')->nullable();
            $table->string('category',50)->default('Lead Facilitator');
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('study_academic_year_id')->references('id')->on('study_academic_years')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('program_module_assignment_id','prog_mod_assign')->references('id')->on('program_module_assignments')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_assignments');
    }
}
