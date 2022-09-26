<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->string('gfs_code');
            $table->string('gl_code');
            $table->string('payment_option');
            $table->mediumInteger('duration');
            $table->string('description');
            $table->tinyInteger('is_external');
            $table->tinyInteger('is_internal');
            $table->tinyInteger('is_paid_per_semester');
            $table->tinyInteger('is_paid_only_once');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_types');
    }
}
