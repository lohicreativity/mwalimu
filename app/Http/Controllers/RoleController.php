<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Domain\Settings\Models\SystemModule;
use App\Domain\Settings\Actions\RoleAction;
use App\Utils\Util;
use Validator;

class RoleController extends Controller
{
    /**
     * Display a list of rolees
     */
    public function index()
    {
    	$data = [
           'roles'=>Role::with('permissions')->paginate(20)
    	];
    	return view('dashboard.settings.roles',$data)->withTitle('Roles');
    }

    /**
     * Store role into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:roles',
            'display_name'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new RoleAction)->store($request);

        return Util::requestResponse($request,'Role created successfully');
    }

    /**
     * Update specified role
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'display_name'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new RoleAction)->update($request);

        return Util::requestResponse($request,'Role updated successfully');
    }

    /**
     * Display a list of permissions for a role
     */
    public function showPermissions(Request $request, $id)
    {
    	$data = [
    	   'system_modules'=>SystemModule::all(),
    	   'role'=>Role::with('permissions.systemModule')->find($id),
           'permissions'=>$request->has('system_module_id')? Permission::where('system_module_id',$request->get('system_module_id'))->get() : [],
           'module'=>SystemModule::find($request->get('system_module_id')),
           'request'=>$request
    	];
    	return view('dashboard.settings.role-permissions',$data)->withTitle('Role Permissions');
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(Request $request, $role_id, $perm_id)
    {
        try{
            $role = Role::findOrFail($role_id);
            $role->permissions()->detach([$perm_id]);
            return redirect()->back()->with('message','Permission revoked successfully');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Update permissions
     */
    public function updatePermissions(Request $request)
    {
    	$permissions = Permission::all();
        $modules = SystemModule::all();
        $current_module = SystemModule::with('permissions')->find($request->get('system_module_id'));
    	$permissionIds = [];
        $current_perm_ids = [];
    	$role = Role::find($request->get('role_id'));
        foreach($permissions as $perm){
        	if($request->get('permission_'.$perm->id) == $perm->id){
        		$permissionIds[$perm->system_module_id][] = $perm->id;
        	}
        }
        foreach($current_module->permissions as $perm){
            $current_perm_ids[] = $perm->id;
        }
        $role->permissions()->detach($current_perm_ids);
        $role->permissions()->attach($permissionIds[$request->get('system_module_id')]);
        

    	return Util::requestResponse($request,'Role persmissions updated successfully');
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        try{
            $role = Role::with('users')->findOrFail($id);
            if(count($role->users) != 0){
                return redirect()->back()->with('error','Role has users and cannot be deleted');
            }
            $role->delete();
            return redirect()->back()->with('message','Role deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
