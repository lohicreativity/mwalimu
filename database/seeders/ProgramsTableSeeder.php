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
               'name'=>'BSc. Computer Science',
               'code'=>'CSM',
               'department_id'=>3,
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'name'=>'BSc. Insurance and Risk Management',
               'code'=>'IRM',
               'department_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Program::insert($data);
    }
}
