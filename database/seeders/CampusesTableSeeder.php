<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Campus;

class CampusesTableSeeder extends Seeder
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
               'name'=>'Kivukoni Campus',
               'abbreviation'=>'Main Campus',
               'region_id'=>2,
               'district_id'=>11,
               'ward_id'=>273,
               'street'=>'Kigamboni',
               'phone'=>'255(22)2820041/47',
               'email'=>'rector@mnma.ac.tz',
               'created_at'=>now(),
               'updated_at'=>now()
            ],
            [
               'name'=>'Zanzibar Campus',
               'abbreviation'=>'Zanzibar Campus',
               'region_id'=>8,
               'district_id'=>40,
               'ward_id'=>1100,
               'street'=>'Bubuni',
               'phone'=>'255(22)2820041/47',
               'email'=>'headznz@mnma.ac.tz',
               'created_at'=>now(),
               'updated_at'=>now()
            ],
            [
               'name'=>'Pemba Branch',
               'abbreviation'=>'Pemba Branch',
               'region_id'=>12,
               'district_id'=>63,
               'ward_id'=>1638,
               'street'=>'Chake Chake Town',
               'phone'=>'255(22)2820041/47',
               'email'=>'headznz@mnma.ac.tz',
                'created_at'=>now(),
                'updated_at'=>now()
             ]
         ];

        Campus::insert($data);
    }
}
