<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('extension',10)->nullable();
            $table->string('mime_type',30)->nullable();
            $table->string('size',50)->nullable();
            $table->unsignedBigInteger('filable_id')->default(0);
            $table->string('filable_type',50)->nullable();
            $table->unsignedBigInteger('module_assignment_id');
            $table->unsignedBigInteger('uploaded_by_user_id');
            $table->timestamps();

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
        Schema::dropIfExists('results_files');
    }
}
