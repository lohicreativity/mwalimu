<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Academic\Models\Award;

class AwardsTableSeeder extends Seeder
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
              'name'=>'BACHELOR OF DEGREE IN COMPUTER SCIENCE',
              'code'=>'CSD',
              'level_id'=>4,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>2,
              'name'=>'BACHELOR OF DEGREE IN INSURANCE AND RISK MANAGEMENT',
              'code'=>'IRM',
              'level_id'=>4,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Award::insert($data);
    }
}
