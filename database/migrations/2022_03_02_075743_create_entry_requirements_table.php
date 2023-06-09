<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntryRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entry_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_window_id');
            $table->unsignedBigInteger('campus_program_id');
            $table->decimal('equivalent_gpa')->nullable();
            $table->integer('equivalent_majors')->nullable();
            $table->integer('equivalent_average_grade')->nullable();
            $table->decimal('open_equivalent_gpa')->nullable();
            $table->integer('open_equivalent_pass_subjects')->nullable();
            $table->integer('open_equivalent_average_grade')->nullable();
            $table->decimal('principle_pass_points')->nullable();
            $table->integer('principle_pass_subjects')->nullable();
            $table->integer('min_principle_pass_subjects')->nullable();
            $table->integer('pass_subjects')->nullable();
            $table->integer('min_pass_subjects')->nullable();
            $table->string('pass_grade',4)->nullable();
            $table->string('award_level',4)->nullable();
            $table->string('award_division',4)->nullable();
            $table->text('exclude_subjects')->nullable();
            $table->text('must_subjects')->nullable();
            $table->text('subsidiary_subjects')->nullable();
            $table->text('principle_subjects')->nullable();
            $table->integer('max_capacity');
            $table->string('group_id')->nullable();
            $table->timestamps();

            $table->foreign('application_window_id')->references('id')->on('application_windows')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('campus_program_id')->references('id')->on('campus_program')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entry_requirements');
    }
}
