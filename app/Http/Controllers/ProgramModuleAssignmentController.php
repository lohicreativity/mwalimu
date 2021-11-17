<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Actions\ProgramModuleAssignmentAction;
use App\Utils\Util;
use Validator;

class ProgramModuleAssignmentController extends Controller
{
    /**
     * Display program module assignments
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campuses'=>Campus::with(['campusPrograms.program'])->get()
    	];
    	return view('dashboard.academic.program-module-assignments',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Assign program modules
     */
    public function assignModules(Request $request, $ac_year_id,$campus_prog_id)
    {
    	$data = [
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($ac_year_id),
            'campus_program'=>CampusProgram::with('program')->find($campus_prog_id),
            'modules'=>Module::all(),
            'semesters'=>Semester::all(),
            'assignments'=>ProgramModuleAssignment::with(['module','semester'])->where('study_academic_year_id',$ac_year_id)->where('campus_program_id',$campus_prog_id)->get()
    	];
    	return view('dashboard.academic.assign-program-modules',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Store program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'year_of_study'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ProgramModuleAssignmentAction)->store($request);

        return Util::requestResponse($request,'Program module assignment created successfully');
    }

    /**
     * Update specified program
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'year_of_study'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return response()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ProgramModuleAssignmentAction)->update($request);

        return Util::requestResponse($request,'Program module assignment updated successfully');
    }

    /**
     * Remove the specified program module assignment
     */
    public function destroy($id)
    {
        try{
            $program = ProgramModuleAssignment::findOrFail($id);
            $program->delete();
            return redirect()->back()->with('message','Program module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }

}
