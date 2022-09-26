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
              'name'=>'Semester 1',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Semester 2',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Semester::insert($data);
    }
}
