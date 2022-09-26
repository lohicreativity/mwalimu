<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UsersTableSeeder extends Seeder
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
              'name'=>'Amani Ghachocha',
              'email'=>'amanighachocha@gmail.com',
              'password'=>Hash::make('admin123'),
              'email_verified_at'=>now(),
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Amani Ghachocha',
              'email'=>'amanighachocha@yahoo.com',
              'password'=>Hash::make('admin123'),
              'email_verified_at'=>now(),
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'Martin Lyuba',
              'email'=>'lyubamt@gmail.com',
              'password'=>Hash::make('admin123'),
              'email_verified_at'=>now(),
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        User::insert($data);
    }
}
