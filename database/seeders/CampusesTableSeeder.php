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
               'name'=>'Main Campus (Dar)',
               'abbreviation'=>'MAIN',
               'region_id'=>4,
               'district_id'=>1,
               'ward_id'=>1,
               'street'=>'Kinondoni',
               'phone'=>'+255759623399',
               'email'=>'info@mnma.ac.tz',
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Campus::insert($data);
    }
}
