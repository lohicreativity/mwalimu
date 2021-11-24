<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Models\User;
use Auth, Validator;

class StudentController extends Controller
{
	/**
	 * Display student dashboard 
	 */
	public function index()
	{
		$data = [
            'student'=>User::find(Auth::user()->id)->student
		];
		return view('dashboard.student.home',$data)->withTitle('Dashboard');
	}

	/**
	 * Display login form
	 */
	public function showLogin(Request $request)
	{
        return view('auth.student-login')->withTitle('Student Login');
	}

    /**
     * Authenticate student
     */
    public function authenticate(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'registration_number'=>'required',
            'password'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $credentials = [
            'username'=>$request->get('registration_number'),
            'password'=>$request->get('password')
        ];
        if(Auth::attempt($credentials)){
        	return redirect()->to('student/dashboard')->with('message','Logged in successfully');
        }else{
           return redirect()->back()->with('error','Incorrect registratin number or password');
        }
    }


    /**
     * Display modules
     */
    public function showModules(Request $request)
    {
    	$student = User::find(Auth::user()->id)->student;
    	$campus = CampusProgram::find($student->campus_program_id)->campus;
    	$program = CampusProgram::find($student->campus_program_id)->program;
    	$study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','academicYear'])->where('status','ACTIVE')->first();
    	$data = [
            'student'=>$student,
            'study_academic_year'=>$study_academic_year,
            'semesters'=>Semester::with(['electivePolicies'=>function($query) use ($student,$study_academic_year){
                    $query->where('campus_program_id',$student->campus_program_id)->where('study_academic_year_id',$study_academic_year->id);
                },'electiveDeadlines'=>function($query) use($student,$study_academic_year,$campus,$program){
                    $query->where('campus_id',$campus->id)->where('study_academic_year_id',$study_academic_year->id)->where('award_id',$program->award_id);
                }])->get(),
            'options'=>Student::find($student->id)->options
    	];

    	return view('dashboard.student.modules',$data)->withTitle('Modules');
    }

    /**
     * Display results
     */
    public function showResults(Request $request)
    {
    	$data = [
           'student'=>User::find(Auth::user()->id)->student
    	];
    	return view('dashboard.student.results',$data)->withTitle('Results');
    }

    /**
     * Display modules
     */
    public function showPayments(Request $request)
    {
    	$data = [
            'student'=>User::find(Auth::user()->id)->student
    	];
    	return view('dashboard.student.payments',$data)->withTitle('Payments');
    }

    /**
     * Opt elective
     */
    public function optModule(Request $request, $id)
    {
    	try{
    	   $student = User::find(Auth::user()->id)->student;
           $assignment = ProgramModuleAssignment::with('campusProgram.campus')->findOrFail($id);
           $study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','academicYear'])->where('status','ACTIVE')->first();
           $elective_policy = ElectivePolicy::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)->where('campus_program_id',$assignment->campus_program_id)->first();

           $elective_module_limit = ElectiveModuleLimit::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)->where('campus_id',$assignment->campusProgram->campus->id)->first();

           if($elective_module_limit){
           	   if(strtotime($elective_module_limit->deadline) < strtotime(now()->format('Y-m-d'))){
           	   	  return redirect()->back()->with('error','Options selection deadline already passed');
           	   }
           }
           $options = Student::find($student->id)->options;

           if($elective_policy->number_of_options <= count($options)){
              return redirect()->back()->with('error','Options cannot exceed '.$elective_policy->number_of_options);
           }else{
              $assignment->students()->sync([$student->id]);
              return redirect()->back()->with('message','Module opted successfully');
	       }
    	}catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
    	}
    }

    /**
     * Opt elective
     */
    public function resetModuleOption(Request $request, $id)
    {
    	try{
    	   $student = User::find(Auth::user()->id)->student;
           $assignment = ProgramModuleAssignment::findOrFail($id);
           $assignment->students()->detach([$student->id]);
           return redirect()->back()->with('message','Module option detached successfully');
    	}catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
    	}
    }
}
