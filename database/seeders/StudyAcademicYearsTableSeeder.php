<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\StudyAcademicYear;

class StudyAcademicYearsTableSeeder extends Seeder
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
	           'academic_year_id'=>1,
	           'status'=>'INACTIVE',
	           'begin_date'=>'2019-10-30',
	           'end_date'=>'2020-10-31',
	           'created_at'=>now(),
	           'updated_at'=>now()
            ],

            [
	           'academic_year_id'=>2,
	           'status'=>'ACTIVE',
	           'begin_date'=>'2020-10-30',
	           'end_date'=>'2021-10-31',
	           'created_at'=>now(),
	           'updated_at'=>now()
            ]
        ];

        StudyAcademicYear::insert($data);
    }
}
