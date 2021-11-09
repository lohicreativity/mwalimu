<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModuleAssignmentController extends Controller
{
	/**
	 * Display a list of staffs to assign modules
	 */
	public function index()
	{
		$data = [
           'staffs'=>Staff::with('modules')->where('status','ACTIVE')->get(),
           'academic_years'=>AcademicYear::all()
		];
		return view('dashboard.academic.assign-staff-modules',$data)->withTitle('Staff Module Assignment');
	}
    /**
     * Search for specified academic year
     */
    public function searchAcademicYear(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'academic_year_id'=>'required',
        ],
        [
            'academic_year_id'=>'Academic year must be selected'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $year = AcademicYear::find($request->get('academic_year_id'));
        return redirect()->back()->with('academic_year',$year);
    }

    /**
     * Update staff module assignment for specified academic year
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'academic_year_id'=>'required',
        ],
        [
            'academic_year_id'=>'Academic year must be selected'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $modules = Module::all();
        $staff = Staff::find($request->get('staff_id'));

        foreach ($modules as $module) {
        	if($request->has('staff_'.$staff->id.'_module_'.$module->id)){
        		$moduleIds[] = $request->get('staff_'.$staff->id.'_module_'.$module->id);
        	}
        }

        if(count($moduleIds) == 0){
            return redirect()->back()->with('error','Please select modules to assign');
        }else{
        	$year->modules()->sync($moduleIds);

    	    return redirect()->back()->with('message','Modules assigned successfully');
        }
    }
}
