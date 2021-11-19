<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Registration\Models\StudentshipStatus;

class StudentshipStatusesTableSeeder extends Seeder
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
              'name'=>'ACTIVE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'GRADUATE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'POSTPONED',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'RESUMED',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'GRADUANT',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'DECEASED',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        StudentshipStatus::insert($data);
    }
}
