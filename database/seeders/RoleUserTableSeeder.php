<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class RoleUserTableSeeder extends Seeder
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
	           'user_id'=>1,
	           'role_id'=>1,
	           'created_at'=>now(),
	           'updated_at'=>now()
            ],

            [
	           'user_id'=>2,
	           'role_id'=>2,
	           'created_at'=>now(),
	           'updated_at'=>now()
            ],

            [
	           'user_id'=>3,
	           'role_id'=>3,
	           'created_at'=>now(),
	           'updated_at'=>now()
            ]
        ];

        DB::table('role_user')->insert($data);
    }
}
