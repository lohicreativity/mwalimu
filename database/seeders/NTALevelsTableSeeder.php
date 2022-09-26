<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\NTALevel;

class NTALevelsTableSeeder extends Seeder
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
              'name'=>'NTA Level 4',
              'min_duration'=>1,
              'max_duration'=>1,
              'award_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'NTA Level 5',
              'min_duration'=>1,
              'max_duration'=>1,
              'award_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'NTA Level 6',
              'min_duration'=>1,
              'max_duration'=>1,
              'award_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'NTA Level 7',
              'min_duration'=>2,
              'max_duration'=>2,
              'award_id'=>3,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'NTA Level 8',
              'min_duration'=>1,
              'max_duration'=>1,
              'award_id'=>4,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        NTALevel::insert($data);
    }
}
