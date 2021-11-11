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
              'name'=>'CERTIFICATE',
              'code'=>'CT',
              'level_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>2,
              'name'=>'DIPLOMA',
              'code'=>'DP',
              'level_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>3,
              'name'=>'BACHELOR DEGREE',
              'code'=>'BD',
              'level_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>4,
              'name'=>'MASTERS DEGREE',
              'code'=>'MD',
              'level_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Award::insert($data);
    }
}
