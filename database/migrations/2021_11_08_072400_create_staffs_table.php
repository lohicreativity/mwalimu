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
            $table->string('gender',2);
            $table->date('birth_date')->nullable();
            $table->text('qualification')->nullable();
            $table->string('category');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('nin');
            $table->string('pf_number')->nullable();
            $table->string('vote_number')->nullable();
            $table->string('check_number')->nullable();
            $table->string('block')->nullable();
            $table->string('room')->nullable();
            $table->string('floor')->nullable();
            $table->string('schedule')->default('FULLTIME');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('street')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('campus_id');
            $table->unsignedBigInteger('disability_status_id');
            $table->string('marital_status',20)->default('SINGLE');
            $table->string('image')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('designation_id')->references('id')->on('designations')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('campus_id')->references('id')->on('campuses')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('disability_status_id')->references('id')->on('disability_statuses')->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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
