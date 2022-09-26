<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ExaminationIrregularity;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Semester;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Actions\ExaminationIrregularityAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ExaminationIrregularityController extends Controller
{
    /**
     * Display a list of examinations
     */
    public function index(Request $request, $mod_assign_id)
    {
    	try{
	    	$data = [
	    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
	           'irregularities'=>ExaminationIrregularity::with(['semester','studyAcademicYear.academicYear','moduleAssignment.module'])->paginate(20),
	           'module_assignment'=>ModuleAssignment::with(['module','programModuleAssignment'])->findOrFail($mod_assign_id),
	           'student'=>$request->has('registration_number')? Student::where('registration_number',$request->get('registration_number'))->first() : null,
	           'semesters'=>Semester::all(),
	           'staff'=>User::find(Auth::user()->id)->staff
	    	];
	    	return view('dashboard.academic.examination-irregularities',$data)->withTitle('Examination Irregularities');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Display program module assignments
     */
    public function showProgramModuleIrregularities(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campuses'=>Campus::all(),
           'campus'=>Campus::with(['campusPrograms.program'=>function($query) use ($request){
                  $query->where('name','LIKE','%'.$request->get('query').'%')->orWhere('code','LIKE','%'.$request->get('query').'%');
           },'campusPrograms.programModuleAssignments.module'])->find($request->get('campus_id')),
           'staff'=>$staff,
           'request'=>$request
        ];
        return view('dashboard.academic.program-module-irregularities',$data)->withTitle('Program Module Irregularities');
    }

    /**
     * Assign program module irregularities
     */
    public function assignIrregularities(Request $request, $ac_year_id,$campus_prog_id)
    {
        $assignments = ProgramModuleAssignment::with(['module','semester'])->where('study_academic_year_id',$ac_year_id)->where('campus_program_id',$campus_prog_id)->get();
        $campus_program = CampusProgram::with('program')->find($campus_prog_id);
        $data = [
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($ac_year_id),
            'campus_program'=>$campus_program,
            'semesters'=>Semester::all(),
            'assignments'=>$assignments,
            'staff'=>User::find(Auth::user()->id)->staff
        ];
        return view('dashboard.academic.program-module-examination-irregularities',$data)->withTitle('Program Module Assignment');
    }

    /**
     * Store examination into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'description'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ExaminationIrregularity::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('student_id',$request->get('student_id'))->count() != 0){
        	return redirect()->back()->with('error','Irregularity already added for this module and this student');
        }


        (new ExaminationIrregularityAction)->store($request);

        return Util::requestResponse($request,'Examination irregularity created successfully');
    }

    /**
     * Update specified examination
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'description'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ExaminationIrregularityAction)->update($request);

        return Util::requestResponse($request,'Examination irregularity updated successfully');
    }

    /**
     * Remove the specified examination
     */
    public function destroy(Request $request, $id)
    {
        try{
            $examination = ExaminationIrregularity::findOrFail($id);
            $examination->delete();
            return redirect()->back()->with('message','Examination  irregularity deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }
}
