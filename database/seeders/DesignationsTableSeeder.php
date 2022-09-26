<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\HumanResources\Models\Designation;

class DesignationsTableSeeder extends Seeder
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
               'name'=>'Lecturer',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'name'=>'Assistant Lecturer',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'name'=>'Senior Lecturer',
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Designation::insert($data);
    }
}
