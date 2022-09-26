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
              'name'=>'Application',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>2,
              'name'=>'Selection',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>3,
              'name'=>'Registration',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>4,
              'name'=>'Teaching',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>5,
              'name'=>'Results',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>6,
              'name'=>'Transcripts',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>7,
              'name'=>'Human Resources',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>8,
              'name'=>'Academic Settings',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'id'=>9,
              'name'=>'General Settings',
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];


        SystemModule::insert($data);
    }
}
