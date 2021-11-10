<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Level;

class LevelsTableSeeder extends Seeder
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
              'name'=>'CERTIFICATE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'DIPLOMA',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'UNDERGRADUATE',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'POSTGRADUATE',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Level::insert($data);
    }
}
