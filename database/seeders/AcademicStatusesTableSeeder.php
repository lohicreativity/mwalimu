<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\AcademicStatus;

class AcademicStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
              'name'=>'PASS',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'RETAKE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'CARRY OVER',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'SUPP',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'FAILED&DISCO',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'ABSCOND',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'INCOMPLETE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

        ];

        AcademicStatus::insert($data);
    }
}
