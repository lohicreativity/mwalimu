<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Actions\ElectivePolicyAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;


class ElectivePolicyController extends Controller
{
    /**
     * Display a list of policys
     */
    public function index(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'elective_policies'=>ElectivePolicy::whereHas('CampusProgram.program.departments',function($query) use($staff){
              $query->where('id',$staff->department_id);
           })->with(['campusProgram.program.departments','campusProgram.campus','semester','studyAcademicYear.academicYear'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->paginate(20),
           'campus_programs'=>CampusProgram::with(['program','campus'])->get(),
           'semesters'=>Semester::all(),
           'staff'=>$staff,
           'request'=>$request
    	];
    	return view('dashboard.academic.elective-policies',$data)->withTitle('Elective Policies');
    }

    /**
     * Store elective policy into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'number_of_options'=>'required|numeric|min:1'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ElectivePolicy::where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'))->where('campus_program_id',$request->get('campus_program_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
          return redirect()->back()->with('error','Elective policy already exists for this programme');
        }

        if(ProgramModuleAssignment::where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'))->where('campus_program_id',$request->get('campus_program_id'))->where('category','OPTIONAL')->count() <= $request->get('number_of_options')){
        	return redirect()->back()->with('error','Number of options exceed elective modules');
        }


        (new ElectivePolicyAction)->store($request);

        return Util::requestResponse($request,'Elective policy created successfully');
    }

    /**
     * Update specified policy
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'number_of_options'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ProgramModuleAssignment::where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'))->where('campus_program_id',$request->get('campus_program_id'))->where('category','OPTIONAL')->count() <= $request->get('number_of_options')){
        	return redirect()->back()->with('error','Number of options exceed elective modules');
        }


        (new ElectivePolicyAction)->update($request);

        return Util::requestResponse($request,'Elective policy updated successfully');
    }

    /**
     * Remove the specified policy
     */
    public function destroy(Request $request, $id)
    {
        try{
            $policy = ElectivePolicy::findOrFail($id);
            $policy->delete();
            return redirect()->back()->with('message','Elective policy deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
