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
              'name'=>'Department of Economic Studies',
              'abbreviation'=>'ES',
              'description'=>'Department of Economic Studies',
              'unit_category_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ],
 
            [
                'id'=>2,
              'name'=>'Department of Education',
              'abbreviation'=>'ED',
              'description'=>'Department of Education',
               'unit_category_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ],
 
            [
                'id'=>3,
              'name'=>'Department of Gender Studies',
              'abbreviation'=>'GS',
              'description'=>'Department of Gender Studies',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],
            [
              'id'=>4,
              'name'=>'Department of Social Studies',
              'abbreviation'=>'SS',
              'description'=>'Department of Social Studies',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],
            [
              'id'=>5,
              'name'=>'Department of ICT',
              'abbreviation'=>'IT',
              'description'=>'Department of ICT',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],
            [
              'id'=>6,
              'name'=>'Department of Library and Publications',
              'abbreviation'=>'LP',
              'description'=>'Department of Library and Publications',
              'unit_category_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],
           [
              'id'=>7,
              'name'=>'Chinese Language Unit',
              'abbreviation'=>'CL',
              'description'=>'Chinese Language Unit',
              'unit_category_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ]


         ];

        Department::insert($data);
    }
}
