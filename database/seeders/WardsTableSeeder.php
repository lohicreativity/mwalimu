<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Ward;

class WardsTableSeeder extends Seeder
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
               'name'=>'Kiseke',
               'district_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'name'=>'Kinondoni',
               'district_id'=>1,
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Ward::insert($data);
    }
}
