<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\District;

class DistrictsTableSeeder extends Seeder
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
               'name'=>'Kinondoni',
               'region_id'=>1,
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>2,
               'name'=>'Ilemela',
               'region_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        District::insert($data);
    }
}
