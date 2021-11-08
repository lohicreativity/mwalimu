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
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('assigned_staff_id')->nullable();
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onUpdate('cascade')->onDelete('cascade');
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
