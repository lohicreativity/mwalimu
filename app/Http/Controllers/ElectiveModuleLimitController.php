<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Actions\ElectiveModuleLimitAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ElectiveModuleLimitController extends Controller
{
    /**
     * Display a list of limits
     */
    public function index(Request $request)
    { return StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id'));
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'elective_module_limits'=>ElectiveModuleLimit::with(['campus','semester','studyAcademicYear.academicYear','award'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->paginate(20),
           'campuses'=>Campus::all(),
           'semesters'=>Semester::all(),
           'awards'=>Award::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.elective-module-limits',$data)->withTitle('Elective Module Limits');
    }

    /**
     * Store elective limit into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'deadline'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(strtotime(now()->format('Y-m-d')) > strtotime($request->get('deadline'))){
            return redirect()->back()->with('error','Deadline cannot be previous date than today');
        }

        if(ElectiveModuleLimit::where('award_id',$request->get('award_id'))->where('campus_id',$request->get('campus_id'))->where('semester_id',$request->get('semester_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
            return redirect()->back()->with('error','Elective deadline for this award already created for this academic year');
        }

        (new ElectiveModuleLimitAction)->store($request);

        return Util::requestResponse($request,'Elective module limit created successfully');
    }

    /**
     * Update specified limit
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'deadline'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
 
        if(strtotime(now()->format('Y-m-d')) > strtotime($request->get('deadline'))){
            return redirect()->back()->with('error','Deadline cannot be previous date than today');
        }

        (new ElectiveModuleLimitAction)->update($request);

        return Util::requestResponse($request,'Elective module limit updated successfully');
    }

    /**
     * Remove the specified elective module limit
     */
    public function destroy(Request $request, $id)
    {
        try{
            $limit = ElectiveModuleLimit::findOrFail($id);
            $limit->delete();
            return redirect()->back()->with('message','Elective module limit deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
