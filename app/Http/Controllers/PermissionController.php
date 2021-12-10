<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use App\Domain\Settings\Actions\PermissionAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class PermissionController extends Controller
{
    /**
     * Display a list of permissiones
     */
    public function index()
    {
    	$data = [
           'permissions'=>$request->has('system_module_id')? Permission::where('system_module_id',$request->get('system_module_id'))->get() : [],
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.settings.permissions',$data)->withTitle('Permissions');
    }

    /**
     * Store permission into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:permissions',
            'display_name'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PermissionAction)->store($request);

        return Util::requestResponse($request,'Permission created successfully');
    }

    /**
     * Update specified permission
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'display_name'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new PermissionAction)->update($request);

        return Util::requestResponse($request,'Permission updated successfully');
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id)
    {
        try{
            $permission = Permission::findOrFail($id);
            $permission->delete();
            return redirect()->back()->with('message','Permission deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
