<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->string('code')->unique();
            $table->integer('min_duration');
            $table->integer('max_duration');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('nta_level_id');
            $table->unsignedBigInteger('award_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');
            $table->foreign('nta_level_id')->references('id')->on('nta_levels')->onUpdate('cascade');
            $table->foreign('award_id')->references('id')->on('awards')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('programs');
    }
}
