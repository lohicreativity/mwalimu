<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\SystemModule;
use App\Domain\Settings\Actions\SystemModuleAction;
use App\Models\Permission;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class SystemModuleController extends Controller
{
    /**
     * Display a list of modules
     */
    public function index()
    {
    	$data = [
           'modules'=>SystemModule::with('permissions')->paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.settings.system-modules',$data)->withTitle('System Modules');
    }

    /**
     * Display a list of permissions
     */
    public function showPermissions(Request $request, $id)
    {
    	try{	
	    	$data = [
	    	   'module'=>SystemModule::findOrFail($id),
	           'permissions'=>Permission::where('system_module_id',$id)->paginate(20),
	           'staff'=>User::find(Auth::user()->id)->staff
	    	];
	    	return view('dashboard.settings.system-module-permissions',$data)->withTitle('System Module Permissions');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Store module into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:modules',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new SystemModuleAction)->store($request);

        return Util::requestResponse($request,'System module created successfully');
    }

    /**
     * Update specified module
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new SystemModuleAction)->update($request);

        return Util::requestResponse($request,'System module updated successfully');
    }

    /**
     * Remove the specified module
     */
    public function destroy($id)
    {
        try{
            $module = SystemModule::findOrFail($id);
            $module->delete();
            return redirect()->back()->with('message','System module deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
