<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\Semester;

class SemestersTableSeeder extends Seeder
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
              'name'=>'Semester I',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Semester II',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Semester::insert($data);
    }
}
