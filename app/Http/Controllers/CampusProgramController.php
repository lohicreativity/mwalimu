<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Registration;
use App\Domain\Registration\Models\Student;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Actions\CampusProgramAction;
use App\Models\User;
use App\Utils\Util;
use Validator, PDF, Auth;

class CampusProgramController extends Controller
{
     /**
     * Display a list of programs
     */
    public function index($id)
    {
    	try{
        $staff = User::find(Auth::user()->id)->staff;

        if(!Auth::user()->hasRole('administrator') && $staff->campus_id != $id){
            return redirect()->back()->with('error','Unable to assign programmes because this is not your campus');
        }
        $programIds = [];
        $campus_programs = CampusProgram::with('program')->where('campus_id',$id)->get();
        foreach ($campus_programs as $key => $prog) {
           $programIds[] = $prog->program->id;
        }

	    	$data = [
	           'campus_programs'=>CampusProgram::with('program')->where('campus_id',$id)->paginate(20),
	           'campus'=>Campus::findOrFail($id),
	           'programs'=>Program::all(),
             'filtered_programs'=>Program::get(),
               'staff'=>$staff
	    	];
	    	return view('dashboard.academic.campus-programs',$data)->withTitle('Campus Programs');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Store campus program into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'regulator_code'=>'required|unique:campus_program',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(CampusProgram::where('campus_id',$request->get('campus_id'))->where('program_id',$request->get('program_id'))->count() != 0){
            return redirect()->back()->withInput()->with('error','The programme is already added in this campus');
        }


        (new CampusProgramAction)->store($request);

        return Util::requestResponse($request,'Campus program created successfully');
    }

    /**
     * Update specified campus program
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'regulator_code'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

         if(CampusProgram::where('campus_id',$request->get('campus_id'))->where('program_id',$request->get('program_id'))->count() != 0){
            return redirect()->back()->withInput()->with('error','The programme is already added in this campus');
        }


        (new CampusProgramAction)->update($request);

        return Util::requestResponse($request,'Campus program updated successfully');
    }

    /**
     * Show attendance
     */
    public function showAttendance(Request $request, $id)
    {
        try{
            $campus_program = CampusProgram::with(['program.departments','campus'])->findOrFail($id);
            foreach($campus_program->program->departments as $dpt){
                if($dpt->pivot->campus_id == $campus_program->campus_id){
                    $department = $dpt;
                }
            }
            $data = [
               'registrations'=>$request->has('semester_id')? Registration::with(['student'])->whereHas('student.campusProgram',function($query) use($id){
                      $query->where('id',$id);
                   })->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'))->get() : Registration::with(['student'])->whereHas('student.campusProgram',function($query) use($id){
                      $query->where('id',$id);
                   })->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->get(),
               'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')),
               'campus_program'=>$campus_program,
               'department'=>$department,
               'request'=>$request
            ];
            return view('dashboard.academic.reports.students-in-campus-program', $data);
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Remove the specified program
     */
    public function destroy(Request $request, $id)
    {
        try{
            $program = CampusProgram::findOrFail($id);
            if(ProgramModuleAssignment::where('campus_program_id',$program->id)->count() != 0 || Student::where('campus_program_id',$program->id)->count() != 0){
               return redirect()->back()->with('error','Campus program cannot be deleted because it has program mudule assignments or students');
            }else{
               $program->delete();
               return redirect()->back()->with('message','Campus program deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
