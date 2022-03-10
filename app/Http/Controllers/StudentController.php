<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\Invoice;
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
           return redirect()->back()->with('error','Incorrect registration number or password');
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
    	if(!$study_academic_year){
    		return redirect()->back()->with('error','No active academic year');
    	}
    	$data = [
            'student'=>$student,
            'study_academic_year'=>$study_academic_year,
            'semesters'=>Semester::with(['electivePolicies'=>function($query) use ($student,$study_academic_year){
                    $query->where('campus_program_id',$student->campus_program_id)->where('study_academic_year_id',$study_academic_year->id);
                },'electiveDeadlines'=>function($query) use ($study_academic_year,$campus,$program){
                    $query->where('campus_id',$campus->id)->where('study_academic_year_id',$study_academic_year->id)->where('award_id',$program->award_id);
                }])->get(),
            'options'=>Student::find($student->id)->options
    	];

    	return view('dashboard.student.modules',$data)->withTitle('Modules');
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
     * Display modules
     */
    public function showProfile(Request $request)
    {
    	$data = [
            'student'=>User::find(Auth::user()->id)->student()->with(['applicant.country','applicant.district','applicant.ward','campusProgram.campus','disabilityStatus'])->first()
    	];
    	return view('dashboard.student.profile',$data)->withTitle('Profile');
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
           $options = Student::find($student->id)->options()->where('semester_id',$assignment->semester_id)->get();

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

     /**
     * Display student module results 
     */
    public function showResultsReport(Request $request)
    {
    	$student = User::find(Auth::user()->id)->student;
    	$results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();

    	$years = [];
    	$years_of_studies = [];
    	$academic_years = [];
    	foreach($results as $key=>$result){
    		if(!array_key_exists($result->moduleAssignment->programModuleAssignment->year_of_study, $years)){
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study] = [];  
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
    		}
            if(!in_array($result->moduleAssignment->studyAcademicYear->id, $years[$result->moduleAssignment->programModuleAssignment->year_of_study])){

            	$years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
            }
    	}

    	foreach($years as $key=>$year){
    		foreach ($year as $yr) {
    			$years_of_studies[$key][] = StudyAcademicYear::with('academicYear')->find($yr);
    		}
    	}

    	$data = [
    	   'years_of_studies'=>$years_of_studies,
           'student'=>$student
    	];
    	return view('dashboard.student.examination-results',$data)->withTitle('Examination Results');
    }

    /**
     * Display student academic year results
     */
    public function showAcademicYearResults(Request $request, $ac_yr_id, $yr_of_study)
    {
    	 $student = User::find(Auth::user()->id)->student;
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student){
         	   $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	   $query->where('id',$student->id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();

          $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->get();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'publications'=>$publications,
         	'optional_programs'=>$optional_programs,
            'student'=>$student
         ];
         return view('dashboard.student.examination-results-report',$data)->withTitle('Examination Results');
    }


    /**
     * Display student overall results
     */
    public function showStudentOverallResults(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
         $student = Student::with(['campusProgram.program'])->find($student_id);
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study){
           $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student_id){
             $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
           $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults'=>function($query){
            $query->latest();
         }])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
             $query->where('id',$student_id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student_id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         //   $optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study){
                   $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY');
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment.students',function($query) use($student){
                     $query->where('id',$student->id);
                 })->whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study){
                     $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL');
                })->get();

            $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->get();

              $moduleIds = [];
              foreach ($core_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }

              foreach ($opt_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }
              
              $missing_modules = [];
              foreach ($core_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }
              foreach ($opt_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }

         $data = [
          'semesters'=>$semesters,
          'annual_remark'=>$annual_remark,
          'results'=>$results,
          'year_of_study'=>$yr_of_study,
          'study_academic_year'=>$study_academic_year,
          'core_programs'=>$core_programs,
          'optional_programs'=>$optional_programs,
          'missing_modules' => $missing_modules,
          'student'=>$student,
          'publications'=>$publications,
          'staff'=>User::find(Auth::user()->id)->staff
         ];
         return view('dashboard.student.examination-results-overall-report',$data)->withTitle('Student Overall Results');
    }

    /**
     * Results appeal
     */
    public function requestControlNumber(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $data = [
           'fee_types'=>FeeType::all(),
           'student'=>$student,
           'invoices'=>Invoice::where('payable_id',$student->id)->where('payable_type','student')->with(['feeType'])->latest()->paginate(20)
        ];
        return view('dashboard.student.request-control-number',$data)->withTItle('Request Control Number');
    }

    /**
     * Logout student
     */
    public function logout(Request $request)
    {
    	Auth::logout();
    	$request->session()->invalidate();
        $request->session()->regenerateToken();
    	return redirect()->to('student/login');
    }
}
