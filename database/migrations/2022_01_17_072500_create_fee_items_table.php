<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->tinyInteger('is_mandatory');
            $table->smallInteger('payment_order');
            $table->unsignedBigInteger('fee_type_id');
            $table->timestamps();

            $table->foreign('fee_type_id')->references('id')->on('fee_types')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_items');
    }
}
