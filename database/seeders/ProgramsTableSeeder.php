<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\Program;

class ProgramsTableSeeder extends Seeder
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
               'name'=>'Basic Technician Certificate In Economics Development',
               'code'=>'BTCED',
               'description'=>'Basic Technician Certificate In Economics Development',
               'department_id'=>1,
               'min_duration'=>1,
               'max_duration'=>1,
                'nta_level_id'=>1,
                'award_id'=>1,
                'category'=>'NON-COMMUNITY DEVELOPMENT',
                'created_at'=>now(),
                'updated_at'=>now()
             ],

            [
               'name'=>'Ordinary Diploma In Economics Development',
               'code'=>'ODED',
               'description'=>'Ordinary Diploma In Economics Development',
               'department_id'=>1,
               'min_duration'=>2,
               'max_duration'=>2,
               'nta_level_id'=>3,
               'award_id'=>2,
               'category'=>'NON-COMMUNITY DEVELOPMENT',
               'created_at'=>now(),
               'updated_at'=>now()
            ],
             [
               'name'=>'Bachelor In Economics Development',
               'code'=>'BD.ED',
               'description'=>'Bachelor In Economics Development',
               'department_id'=>1,
               'min_duration'=>3,
               'max_duration'=>5,
               'nta_level_id'=>5,
               'award_id'=>3,
               'category'=>'NON-COMMUNITY DEVELOPMENT',
               'created_at'=>now(),
               'updated_at'=>now()
            ],


         ];

        Program::insert($data);
    }
}
