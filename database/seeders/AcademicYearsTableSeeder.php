<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\AcademicYear;

class AcademicYearsTableSeeder extends Seeder
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
               'year'=>'2019/2020',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'year'=>'2020/2021',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'year'=>'2021/2022',
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        AcademicYear::insert($data);
    }
}
