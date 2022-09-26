<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\Intake;

class IntakesTableSeeder extends Seeder
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
              'name'=>'September',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'March',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Intake::insert($data);
    }
}
