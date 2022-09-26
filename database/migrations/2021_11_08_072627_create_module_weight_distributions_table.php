<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModuleWeightDistributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_weight_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('marks');
            $table->unsignedBigInteger('module_id');
            $table->timestamps();

            $table->foreign('module_id')->references('id')->on('modules')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_weight_distributions');
    }
}
