<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Region;

class RegionsTableSeeder extends Seeder
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
               'name'=>'Dar Es Salaam',
               'country_id'=>1,
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>2,
               'name'=>'Mwanza',
               'country_id'=>1,
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>3,
               'name'=>'Nairobi',
               'country_id'=>2,
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Region::insert($data);
    }
}
