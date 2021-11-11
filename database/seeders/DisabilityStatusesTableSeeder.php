<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\DisabilityStatus;

class DisabilityStatusesTableSeeder extends Seeder
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
              'name'=>'None',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Visual Imparement',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Hearing Imparement',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        DisabilityStatus::insert($data);
    }
}
