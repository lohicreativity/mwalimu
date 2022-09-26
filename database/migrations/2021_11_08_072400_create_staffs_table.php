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
            $table->string('title',10);
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('surname');
            $table->string('gender',2);
            $table->date('birth_date');
            $table->string('category');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('nin')->nullable();
            $table->string('pf_number');
            $table->string('block');
            $table->string('room');
            $table->string('floor');
            $table->string('schedule')->default('FULLTIME');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('ward_id');
            $table->string('street');
            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('disability_status_id');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('designation_id')->references('id')->on('designations')->onUpdate('cascade');

            $table->foreign('campus_id')->references('id')->on('campuses')->onUpdate('cascade');

            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade');

            $table->foreign('disability_status_id')->references('id')->on('disability_statuses')->onUpdate('cascade');

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
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
