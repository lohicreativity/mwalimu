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
              'abbreviation'=>'AD',
              'unit_category_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>2,
              'name'=>'Banking & Finance',
              'abbreviation'=>'BF',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>3,
              'name'=>'Computer Systems',
              'abbreviation'=>'CS',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Department::insert($data);
    }
}
