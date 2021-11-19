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
               'created_at'=>now(),
               'updated_at'=>now()
           ],

           [
               'name'=>'AAR',
               'created_at'=>now(),
               'updated_at'=>now()
           ]
        ];

        Insurance::insert($data);
    }
}
