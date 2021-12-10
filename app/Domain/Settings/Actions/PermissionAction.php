<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Domain\Settings\Repositories\Interfaces\PermissionInterface;

class PermissionAction implements PermissionInterface{
	
	public function store(Request $request){
	         $permission = new Permission;
           $permission->name = $request->get('name');
           $permission->display_name = $request->get('display_name');
           $permission->save();
	}

	public function update(Request $request){
	         $permission = Permission::find($request->get('permission_id'));
           $permission->name = $request->get('name');
           $permission->display_name = $request->get('display_name');
           $permission->save();
	}
}