<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code',10);
            $table->integer('credit');
            // $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('nta_level_id');
            $table->tinyInteger('course_work_based')->default(1);
            $table->string('syllabus')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->foreign('nta_level_id')->references('id')->on('nta_levels')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
}
