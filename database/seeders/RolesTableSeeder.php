<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
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
              'name'=>'administrator',
              'display_name'=>'Adminstrator',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'student',
              'display_name'=>'Student',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'hod',
              'display_name'=>'Head Of Department',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'staff',
              'display_name'=>'Staff',
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'applicant',
              'display_name'=>'Applicant',
              'created_at'=>now(),
              'updated_at'=>now()
           ],
        ];

        Role::insert($data);
    }
}
