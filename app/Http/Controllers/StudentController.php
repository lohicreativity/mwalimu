<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
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
    	$data = [
            'student'=>$student,
            'study_academic_year'=>StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','academicYear'])->where('status','ACTIVE')->first(),
            'semesters'=>Semester::all()
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
}
