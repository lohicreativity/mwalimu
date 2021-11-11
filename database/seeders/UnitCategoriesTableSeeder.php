<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\UnitCategory;

class UnitCategoriesTableSeeder extends Seeder
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
              'name'=>'Office',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>2,
              'name'=>'Department',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
           	  'id'=>3,
              'name'=>'Faculty',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
    	];

        UnitCategory::insert($data);
    }
}
