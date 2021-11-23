<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Registration\Models\Insurance;

class InsurancesTableSeeder extends Seeder
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
               'name'=>'NHIF',
               'description'=>'National Health Insurance Fund',
               'created_at'=>now(),
               'updated_at'=>now()
           ],

           [
               'name'=>'AAR',
               'description'=>'AAR Healthcare',
               'created_at'=>now(),
               'updated_at'=>now()
           ]
        ];

        Insurance::insert($data);
    }
}
