<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Actions\ModuleAction;
use App\Utils\Util;
use Validator;

class ModuleController extends Controller
{
    /**
     * Display a list of modules
     */
    public function index(Request $request)
    {
        if($request->has('query')){
            $modules = Module::with(['department','ntaLevel'])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->paginate(20);
        }else{
            $modules = Module::with(['department','ntaLevel'])->paginate(20);
        }
    	$data = [
           'modules'=>$modules,
           'nta_levels'=>NTALevel::all(),
           'departments'=>Department::all(),
           'request'=>$request
    	];
    	return view('dashboard.academic.modules',$data)->withTitle('Modules');
    }

    /**
     * Store module into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:modules',
            'code'=>'required|unique:modules',
            'credit'=>'required|numeric',
            'syllabus'=>'required|mimes:pdf'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new ModuleAction)->store($request);

        return Util::requestResponse($request,'Module created successfully');
    }

    /**
     * Update specified module
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'code'=>'required',
            'credit'=>'required|numeric',
            'syllabus'=>'mimes:pdf'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new ModuleAction)->update($request);

        return Util::requestResponse($request,'Module updated successfully');
    }

    /**
     * Download syllabus
     */
    public function downloadSyllabus(Request $request, $id)
    {
        try{
            $module = Module::findOrFail($id);
            return response()->download(public_path('uploads/'.$module->syllabus));
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified module
     */
    public function destroy($id)
    {
        try{
            $module = Module::findOrFail($id);
            $module->delete();
            return redirect()->back()->with('message','Module deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
