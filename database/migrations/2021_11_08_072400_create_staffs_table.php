<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->date('birth_date')->nullable();
            $table->text('qualification')->nullable();
            $table->string('category');
            $table->string('type');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('staff_id');
            $table->unsignedBigInteger('designation_id');
            $table->string('disability_status',20)->default('NIL');
            $table->timestamps();

            $table->foreign('designation_id')->references('id')->on('designations')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staffs');
    }
}
