<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTamisemiStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tamisemi_students', function (Blueprint $table) {
            $table->id();
            $table->string('f4indexno');
            $table->smallInteger('year');
            $table->string('fullname');
            $table->string('programme_id');
            $table->string('programme_name');
            $table->string('campus');
            $table->string('gender',10);
            $table->date('date_of_birth');
            $table->string('phone_number');
            $table->string('email');
            $table->string('address');
            $table->string('district');
            $table->string('region');
            $table->string('next_of_kin_fullname');
            $table->string('next_of_kin_phone_number');
            $table->string('next_of_kin_email');
            $table->string('next_of_kin_address');
            $table->string('next_of_kin_region');
            $table->string('relationship');
            $table->string('appacyr');
            $table->string('intake',20);
            $table->timestamp('receiveDate');
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
        Schema::dropIfExists('tamisemi_students');
    }
}
