<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Role;
use App\Domain\Settings\Repositories\Interfaces\RoleInterface;

class RoleAction implements RoleInterface{
	
	public function store(Request $request){
	       $role = new Role;
           $role->name = $request->get('name');
           $role->display_name = $request->get('display_name');
           $role->is_system_role = $request->get('is_system_role');
           $role->save();
	}

	public function update(Request $request){
	       $role = Role::find($request->get('role_id'));
           $role->name = $request->get('name');
           $role->display_name = $request->get('display_name');
           $role->is_system_role = $request->get('is_system_role');
           $role->save();
	}
}