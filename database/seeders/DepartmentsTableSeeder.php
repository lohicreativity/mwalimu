<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\Department;

class DepartmentsTableSeeder extends Seeder
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
           	  'id'=>1,
              'name'=>'Admistration',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>2,
              'name'=>'Banking & Finance',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>3,
              'name'=>'Computer Systems & Mathematics',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Department::insert($data);
    }
}
