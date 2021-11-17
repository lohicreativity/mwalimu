<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Settings\Models\SystemModule;

class SystemModulesTableSeeder extends Seeder
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
              'name'=>'Academic',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>2,
              'name'=>'Application',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>3,
              'name'=>'Finance',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>4,
              'name'=>'Human Resources',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>5,
              'name'=>'Registration',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>6,
              'name'=>'Settings',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];


        SystemModule::insert($data);
    }
}
