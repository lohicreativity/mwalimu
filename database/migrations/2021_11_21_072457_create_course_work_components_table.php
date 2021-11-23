<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseWorkComponentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_work_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->smallInteger('quantity');
            $table->unsignedBigInteger('module_assignment_id');
            $table->timestamps();

            $table->foreign('module_assignment_id')->references('id')->on('module_assignments')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_work_components');
    }
}
