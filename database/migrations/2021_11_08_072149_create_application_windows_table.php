                <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationWindowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('application_windows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intake_id');
            $table->unsignedBigInteger('study_academic_year_id');
            $table->integer('capacity');
            $table->date('begin_date');
            $table->date('end_date');
            $table->timestamps();

            $table->foreign('study_academic_year_id','study_ac_year_app_window')->references('id')->on('study_academic_years')->onUpdate('cascade');
            $table->foreign('intake_id')->references('id')->on('intakes')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('application_windows');
    }
}
