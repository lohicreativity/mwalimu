<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
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
              'name'=>'add-module',
              'display_name'=>'Add Module',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'edit-module',
              'display_name'=>'Edit Module',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'delete-module',
              'display_name'=>'Delete Module',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'add-award',
              'display_name'=>'Add Award',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'edit-award',
              'display_name'=>'Edit Award',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'delete-award',
              'display_name'=>'Delete Award',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'add-program',
              'display_name'=>'Add Program',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'edit-program',
              'display_name'=>'Edit Program',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ],

           [
              'name'=>'delete-program',
              'display_name'=>'Delete Program',
              'system_module_id'=>1,
              'created_at'=>now(),
              'updated_at'=>now()
           ]
        ];

        Permission::insert($data);
    }
}
