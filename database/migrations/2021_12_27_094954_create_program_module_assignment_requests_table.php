<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramModuleAssignmentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_module_assignment_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('program_module_assignment_id');
            $table->timestamps();

            $table->foreign('staff_id')->references('id')->on('staffs')->onUpdate('cascade');
            $table->foreign('program_module_assignment_id','prog_mod_assign_request')->references('id')->on('program_module_assignments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('program_module_assignment_requests');
    }
}
