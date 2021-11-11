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
              'id'=>1,
              'name'=>'NTA Level 4',
              'award_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>2,
              'name'=>'NTA Level 5',
              'award_id'=>2,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>3,
              'name'=>'NTA Level 6',
              'award_id'=>3,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>4,
              'name'=>'NTA Level 7',
              'award_id'=>4,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        NTALevel::insert($data);
    }
}
