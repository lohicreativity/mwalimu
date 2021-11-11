<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Country;

class CountriesTableSeeder extends Seeder
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
               'name'=>'Tanzania',
               'code'=>'TZ',
               'nationality'=>'Tanzanian',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>2,
               'name'=>'Kenya',
               'code'=>'KE',
               'nationality'=>'Kenyan',
               'created_at'=>now(),
               'updated_at'=>now()
            ]
        ];

        Country::insert($data);
    }
}
