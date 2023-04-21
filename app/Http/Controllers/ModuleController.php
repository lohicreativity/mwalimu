<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Actions\ModuleAction;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Utils\Util;
use Validator, Auth, File;

class ModuleController extends Controller
{
    /**
     * Display a list of modules
     */
    public function index(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        if($request->has('query')){
            $modules = Module::whereHas('departments',function($query) use($staff){
                 $query->where('campus_id',$staff->campus_id)->where('department_id',$staff->department_id);
            })->with(['departments','ntaLevel'])->where('name','LIKE','%'.$request->get('query').'%')->OrWhere('code','LIKE','%'.$request->get('query').'%')->paginate(20);
        }else{
            $modules = Module::whereHas('departments',function($query) use($staff){
                 $query->where('campus_id',$staff->campus_id)->where('department_id',$staff->department_id);
            })->with(['departments','ntaLevel'])->latest()->paginate(20);
        }
        
    	$data = [
           'modules'=>$modules,
           'nta_levels'=>NTALevel::all(),
           'departments'=>Department::whereHas('campuses',function($query) use($staff){
                 $query->where('id',$staff->campus_id);
            })->get(),
           'staff'=>$staff,
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
		$module = Module::where('code',$request->get('code'))->where('name',$request->get('name'))->first();
		$existing_module_record = Module::with('departments')->where('id', $module->id)
										->whereHas('departments', function($query) use($request){$query->where('campus_id',$request->get('campus_id'));})->first();
		
		if($existing_module_record){
			return redirect()->back()->with('error','The module has already been assigned in '.$existing_module_record->departments[0]->name);
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
            if(!Storage::exists(public_path('uploads/'.$module->syllabus))){
                return redirect()->back()->with('error','Syllabus document not found');
            }
            return response()->download(public_path('uploads/'.$module->syllabus));
        }catch(\Exception $e){
            return redirect()->back()->with('error','Syllabus document not found');
        }
    }

    /**
     * Remove the specified module
     */
    public function destroy($id)
    {
        try{
            $module = Module::with('departments')->findOrFail($id);
            $staff = User::find(Auth::user()->id)->staff;
        
            if(Auth::user()->hasRole('hod') && !Util::collectionContainsKey($module->departments,$staff->department_id)){
                return redirect()->back()->with('error','Unable to delete module because this is not your department');
            }
            if(ProgramModuleAssignment::whereHas('moduleAssignments',function($query){
                   $query->where('course_work_process_status','PROCESSED');
               })->where('module_id',$module->id)->count() != 0){
                return redirect()->back()->with('error','Cannot delete module with coursework');
            }
            $module->departments()->detach([$staff->department_id]);
            ProgramModuleAssignment::whereHas('campusProgram',function($query) use ($staff){
                $query->where('campus_id',$staff->campus_id);
            })->where('module_id',$module->id)->delete();
            return redirect()->back()->with('message','Module deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
