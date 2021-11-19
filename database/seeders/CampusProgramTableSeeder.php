<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\CampusProgram;

class CampusProgramTableSeeder extends Seeder
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
	                  'campus_id'=>1,
	                  'program_id'=>1,
	                  'regulator_code'=>'CSM001',
	                  'created_at'=>now(),
	                  'updated_at'=>now()
	               ],

	               [
	                  'campus_id'=>1,
	                  'program_id'=>2,
	                  'regulator_code'=>'CSM002',
	                  'created_at'=>now(),
	                  'updated_at'=>now()
	               ],
        ];

        CampusProgram::insert($data);
    }
}
