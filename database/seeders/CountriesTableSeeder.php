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
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>2,
               'name'=>'Kenya',
               'code'=>'KE',
               'nationality'=>'Kenyan',
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>3,
               'name'=>'Uganda',
               'code'=>'UG',
               'nationality'=>'Ugandan',
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>4,
               'name'=>'Burundi',
               'code'=>'BR',
               'nationality'=>'Burundian',
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>5,
               'name'=>'Rwanda',
               'code'=>'RW',
               'nationality'=>'Rwandan',
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>6,
               'name'=>'South Sudan',
               'code'=>'SS',
               'nationality'=>'Sudanese',
               'region'=>'EAC',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>7,
               'name'=>'Republic of Congo',
               'code'=>'CG',
               'nationality'=>'Congolese',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>8,
               'name'=>'Democratic Republic of Congo',
               'code'=>'CD',
               'nationality'=>'Congolese',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>9,
               'name'=>'Nigeria',
               'code'=>'NG',
               'nationality'=>'Nigerian',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>10,
               'name'=>'Ghana',
               'code'=>'GH',
               'nationality'=>'Ghanan',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>11,
               'name'=>'Malawi',
               'code'=>'MW',
               'nationality'=>'Malawian',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>12,
               'name'=>'Zambia',
               'code'=>'ZM',
               'nationality'=>'Zambian',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>13,
               'name'=>'Mozambique',
               'code'=>'MZ',
               'nationality'=>'Mozambiquan',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],

            [
               'id'=>14,
               'name'=>'Comoro',
               'code'=>'KM',
               'nationality'=>'Comoran',
               'region'=>'OTHER',
               'created_at'=>now(),
               'updated_at'=>now()
            ],
        ];
        

        Country::insert($data);
    }
}
