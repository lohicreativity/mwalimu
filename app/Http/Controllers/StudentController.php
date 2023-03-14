<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\PerformanceReportRequest;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Registration\Models\Registration;
use App\Domain\Registration\Models\IdCardRequest;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\Applicant;
use App\Domain\Settings\Models\Currency;
use App\Domain\Settings\Models\Campus;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;
use Auth, Hash, Validator, DB;

class StudentController extends Controller
{
	/**
	 * Display student dashboard 
	 */
	public function index()
	{
    $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
		$data = [
            'student'=>$student,
            'loan_allocation'=>LoanAllocation::where('index_number',$student->applicant->index_number)->where('loan_amount','!=',0.00)->where('study_academic_year_id',session('active_academic_year_id'))->first(),
            'registration'=>Registration::where('student_id',$student->id)->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->first(),
            'performance_report'=>PerformanceReportRequest::where('student_id',$student->id)->where('status','ATTENDED')->latest()->first()
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
            'password'=>$request->get('password'),
			      'status'=>'ACTIVE'
        ];
        if(Auth::attempt($credentials)){
          if(Auth::user()->must_update_password == 1){
              return redirect()->to('change-password')->with('message','Logged in successfully');
          }else{
              return redirect()->to('student/dashboard')->with('message','Logged in successfully');
          } 	
        }else{
           return redirect()->back()->with('error','Incorrect registration number or password');
        }
    }


    /**
     * Display modules
     */
    public function showModules(Request $request)
    {
    	$student = User::find(Auth::user()->id)->student()->with(['registrations'=>function($query){
            $query->where('study_academic_year_id',session('active_academic_year_id'))->where('status','REGISTERED');
        },'academicStatus'])->first();
    	$campus = CampusProgram::find($student->campus_program_id)->campus;
    	$program = CampusProgram::find($student->campus_program_id)->program;
    	$study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','moduleAssignments.moduleAssignments.staff','academicYear'])->where('status','ACTIVE')->first();
    	if(!$study_academic_year){
    		return redirect()->back()->with('error','No active academic year');
    	}
		return $study_academic_year; //->moduleAssignments[0]->semester_id;
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
return $data->semesters;
    	return view('dashboard.student.modules',$data)->withTitle('Modules');
    }

    /**
     * Display modules
     */
    public function showPayments(Request $request)
    {
      $student = User::find(Auth::user()->id)->student;
    	$data = [
            'student'=>$student,
            'receipts'=>DB::table('gateway_payments')->join('invoices','gateway_payments.control_no','=','invoices.control_no')->join('fee_types','invoices.fee_type_id','=','fee_types.id')->join('study_academic_years','invoices.applicable_id','=','study_academic_years.id')->join('academic_years','study_academic_years.academic_year_id','=','academic_years.id')->select(DB::raw('gateway_payments.*, fee_types.name as fee_name, academic_years.year as academic_year'))->where(function($query) use($student){
				$query->where('invoices.payable_id',$student->id)->where('invoices.payable_type','student')->where('invoices.applicable_type','academic_year');
			})->orWhere(function($query) use($student){
				$query->where('invoices.payable_id',$student->applicant_id)->where('invoices.payable_type','applicant')->where('invoices.applicable_type','academic_year');
			})->latest()->get()
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
    {	// $id is a program_module_assignment id
    	try{
    	   $student = User::find(Auth::user()->id)->student;
           $assignment = ProgramModuleAssignment::with('campusProgram.campus', 'campusProgram.program')->findOrFail($id);
           $study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','academicYear'])->where('status','ACTIVE')->first();
           $elective_policy = ElectivePolicy::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)->where('campus_program_id',$assignment->campus_program_id)->first();

           $elective_module_limit = ElectiveModuleLimit::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)
		   ->where('campus_id',$assignment->campusProgram->campus->id)->where('award_id', $assignment->campusProgram->program->award_id)->first();

           if($elective_module_limit){
           	   if(strtotime($elective_module_limit->deadline) < strtotime(now()->format('Y-m-d'))){
           	   	  return redirect()->back()->with('error','Options selection deadline already passed');
           	   }
           }
           $options = Student::find($student->id)->options()->where('semester_id',$assignment->semester_id)->get();
//return $options;
           if($elective_policy->number_of_options <= count($options)){
              return redirect()->back()->with('error','Options cannot exceed '.$elective_policy->number_of_options);
           }else{
              $assignment->students()->attach([$student->id]);
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
         'results_present_status'=>count($results) != 0? true : false,
         'student'=>$student
    	];
    	return view('dashboard.student.examination-results',$data)->withTitle('Examination Results');
    }

    /**
     * Display student academic year results
     */
    public function showAcademicYearResults(Request $request, $ac_yr_id, $yr_of_study)
    {
    	 $student = User::find(Auth::user()->id)->student()->with(['registrations'=>function($query) use($ac_yr_id,$yr_of_study){
            $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('status','REGISTERED');
         }])->with(['campusProgram.program'])->first();
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);

         $retake_sem_remarks = SemesterRemark::where('student_id',$student->id)->where('remark','RETAKE')->where('year_of_study',$yr_of_study)->get();
         
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id){
         	   $query->where('study_academic_year_id',$ac_yr_id);
         })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study){
             $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	   $query->where('id',$student->id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();

          $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->where('nta_level_id',$student->campusProgram->program->nta_level_id)->get();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
            'retake_sem_remarks'=>$retake_sem_remarks,
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
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study, $request){
           $query->where('student_id',$student->id)->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $request){
             //$query->where('study_academic_year_id',$ac_yr_id);
             $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });
         })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study, $request){
             $query->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });//->where('study_academic_year_id',$ac_yr_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study, $request){
           $query->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });//->where('study_academic_year_id',$ac_yr_id);
         },'moduleAssignment.specialExams'=>function($query) use($student){
            $query->where('student_id',$student->id);
         },'moduleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults'=>function($query){
            $query->latest();
         }])->where('student_id',$student->id)->get();
         
        // ->where('study_academic_year_id',$ac_yr_id)
       //  where('study_academic_year_id',$ac_yr_id)->
         $core_programs = ProgramModuleAssignment::with(['module'])->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
             $query->where('id',$student_id);
             })->with(['module'])->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
          //where('study_academic_year_id',$ac_yr_id)->
          $annual_remark = AnnualRemark::where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('student_id',$student_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         //   $optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study, $request){
                   $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','COMPULSORY');
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment.students',function($query) use($student){
                     $query->where('id',$student->id);
                 })->whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study, $request){
                     $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
               })->where('year_of_study',$yr_of_study)->where('category','OPTIONAL');
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
          'student'=>User::find(Auth::user()->id)->student
         ];
         return view('dashboard.student.examination-results-overall-report',$data)->withTitle('Student Overall Results');
    }

    /**
     * Show registration
     */
    public function showRegistration(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $data = [
            'student'=>$student,
            'registration'=>Registration::where('student_id',$student->id)->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->first()
        ];
        return view('dashboard.student.registration',$data)->withTitle('Registration');
    }

    /**
     * Request control number
     */
    public function showRequestControlNumber(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $data = [
           'fee_types'=>FeeType::all(),
           'student'=>$student,
           'invoices'=>Invoice::with(['applicable','feeType','gatewayPayment'])->where(function($query) use($student){
			      $query->where('payable_id',$student->id)->where('payable_type','student');
		   })->orWhere(function($query) use($student){
			      $query->where('payable_id',$student->applicant_id)->where('payable_type','applicant');
		   })->latest()->get()
        ];
        return view('dashboard.student.request-control-number',$data)->withTItle('Request Control Number');
    }

    /**
     * Request control number 
     */
    public function requestPaymentControlNumber(Request $request)
    {
        $student = Student::with(['applicant','studentshipStatus','academicStatus','semesterRemarks'=>function($query){
            $query->latest();
        },'semesterRemarks.semester'])->find($request->get('student_id'));
        $email = $student->email? $student->email : 'admission@mnma.ac.tz';

        DB::beginTransaction();
        $study_academic_year = StudyAcademicYear::find(session('active_academic_year_id'));
        $semester = Semester::find(session('active_semester_id'));
        $usd_currency = Currency::where('code','USD')->first();
           
        foreach($student->semesterRemarks as $rem){
          if($student->academicStatus->name == 'RETAKE'){
              if($rem->semester_id == session('active_semester_id') && $rem->remark != 'RETAKE'){
                      return redirect()->back()->with('error','You are not allowed to register for retake in this semester');
              }
          }
       }

        if($student->studentshipStatus->name == 'POSTPONED'){
             return redirect()->back()->with('error','You cannot continue with registration because you have been postponed');
        }
        if($student->studentshipStatus->name == 'GRADUANT'){
            return redirect()->back()->with('error','You cannot continue with registration because you have already graduated');
        }
        if($student->academicStatus->name == 'FAIL&DISCO'){
          return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
        }
        if($student->academicStatus->name == 'ABSCOND'){
          return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }
        if($student->academicStatus->name == 'INCOMPLETE'){
          return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }
        $annual_remarks = AnnualRemark::where('student_id',$student->id)->latest()->get();
        $semester_remarks = SemesterRemark::with('semester')->where('student_id',$student->id)->latest()->get();
        $can_register = true;
        if(count($annual_remarks) != 0){
            $last_annual_remark = $annual_remarks[0];
            $year_of_study = $last_annual_remark->year_of_study;
            if($last_annual_remark->remark == 'RETAKE'){
                $year_of_study = $last_annual_remark->year_of_study;
            }elseif($last_annual_remark->remark == 'CARRY'){
                $year_of_study = $last_annual_remark->year_of_study;
            }elseif($last_annual_remark->remark == 'PASS'){
                if(str_contains($semester_remarks[0]->semester->name,'2')){
                   $year_of_study = $last_annual_remark->year_of_study + 1;
                }else{
                   $year_of_study = $last_annual_remark->year_of_study;
                }
            }elseif($last_annual_remark->remark == 'FAIL&DISCO'){
            $can_register = false;
            return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
          }elseif($last_annual_remark->remark == 'INCOMPLETE'){
            $can_register = false;
            return redirect()->back()->with('error','You cannot continue with registration because you have incomplete results');
          }
        }



        if($request->get('fee_type') == 'TUITION'){
            $existing_tuition_invoice = Invoice::whereHas('feeType',function($query){
                $query->where('name','LIKE','%Tuition%');
            })->where('applicable_type','academic_year')->where('applicable_id',$study_academic_year->id)->where('payable_id',$student->id)->where('payable_type','student')->first();
            
            if($existing_tuition_invoice){
                return redirect()->back()->with('error','You have already requested for tuition fee control number for this academic year');
            }
            
			
			$existing_tuition_invoice = Invoice::whereHas('feeType',function($query){
                $query->where('name','LIKE','%Tuition%');
            })->where('payable_id',$student->applicant_id)->where('payable_type','applicant')->first();

            if($existing_tuition_invoice){
                return redirect()->back()->with('error','You have already requested for tuition fee control number for this academic year');
            }

            $program_fee = ProgramFee::with('feeItem.feeType')->where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$student->campus_program_id)->first();

            if(!$program_fee){
                return redirect()->back()->with('error','Programme fee has not been set');
            }

            $loan_allocation = LoanAllocation::where('index_number',$student->applicant->index_number)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)->first();
            if($loan_allocation){
                 if(str_contains($student->applicant->nationality,'Tanzania')){
                     $amount = $program_fee->amount_in_tzs - $loan_allocation->tuition_fee;
                     $amount_loan = round($loan_allocation->tuition_fee);
                     $currency = 'TZS';
                 }else{
                     $amount = round(($program_fee->amount_in_usd - $loan_allocation->tuition_fee/$usd_currency->factor) * $usd_currency->factor);
                     $amount_loan = round($loan_allocation->tuition_fee);
                     $currency = 'TZS'; //'USD';
                 }
            }else{
                 if($student->academicStatus->name == 'RETAKE'){
                    if(str_contains($student->applicant->nationality,'Tanzania')){
                         $amount = round(0.5*$program_fee->amount_in_tzs);
                         $amount_loan = 0.00;
                         $currency = 'TZS';
                     }else{
                         $amount = round(0.5*$program_fee->amount_in_usd*$usd_currency->factor);
                         $amount_loan = 0.00;
                         $currency = 'TZS'; //'USD';
                     }
                 }else{
                    if(str_contains($student->applicant->nationality,'Tanzania')){
                         $amount = round($program_fee->amount_in_tzs);
                         $amount_loan = 0.00;
                         $currency = 'TZS';
                     }else{
                         $amount = round($program_fee->amount_in_usd*$usd_currency->factor);
                         $amount_loan = 0.00;
                         $currency = 'TZS'; //'USD';
                     }
                 }
                 
            }

                 if(str_contains($student->applicant->nationality,'Tanzania')){
                     $amount_without_loan = round($program_fee->amount_in_tzs);
                 }else{
                     $amount_without_loan = round($program_fee->amount_in_usd*$usd_currency->factor);
                 }
          
            
            if($amount != 0.00){
                  $invoice = new Invoice;
                  $invoice->reference_no = 'MNMA-TF-'.time();
                  $invoice->actual_amount = $amount_without_loan;
                  $invoice->amount = $amount;
                  $invoice->currency = $currency;
                  $invoice->payable_id = $student->id;
                  $invoice->payable_type = 'student';
                  $invoice->applicable_id = $study_academic_year->id;
                  $invoice->applicable_type = 'academic_year';
                  $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
                  $invoice->save();


                  $generated_by = 'SP';
                  $approved_by = 'SP';
                  $inst_id = config('constants.SUBSPCODE');



                  $result = $this->requestControlNumber($request,
                                              $invoice->reference_no,
                                              $inst_id,
                                              $invoice->amount,
                                              $program_fee->feeItem->feeType->description,
                                              $program_fee->feeItem->feeType->gfs_code,
                                              $program_fee->feeItem->feeType->payment_option,
                                              $student->id,
                                              $student->first_name.' '.$student->surname,
                                              $student->phone,
                                              $email,
                                              $generated_by,
                                              $approved_by,
                                              $program_fee->feeItem->feeType->duration,
                                              $invoice->currency);
            }
            
            if(str_contains($student->applicant->programLevel->name,'Bachelor')){
               $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                  $query->where('name','LIKE','%TCU%');
               })->where('study_academic_year_id',$study_academic_year->id)->first();

            }else{
               $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                  $query->where('name','LIKE','%NACTE%');
               })->where('study_academic_year_id',$study_academic_year->id)->first();

            }
            
            $other_fees_tzs = FeeAmount::whereHas('feeItem',function($query){
              $query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
            })->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_tzs');
            $other_fees_usd = FeeAmount::whereHas('feeItem',function($query){
              $query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
            })->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_usd');

            $other_fees_tzs = $other_fees_tzs + $quality_assurance_fee->amount_in_tzs;
            $other_fees_usd = $other_fees_usd + $quality_assurance_fee->amount_in_usd;
            if(str_contains($student->applicant->nationality,'Tanzania')){
              $other_fees = round($other_fees_tzs);
              $currency = 'TZS';
            }else{
              $other_fees = round($other_fees_usd*$usd_currency->factor);
              $currency = 'TZS';//'USD';
            }

            $feeType = FeeType::where('name','LIKE','%Miscellaneous%')->first();

            if(!$feeType){
                return redirect()->back()->with('error','Miscellaneous fee type has not been set');
            }
            
            if($other_fees != 0.00){
                $invoice = new Invoice;
                $invoice->reference_no = 'MNMA-MSC'.time();
                $invoice->actual_amount = $other_fees;
                $invoice->amount = $other_fees;
                $invoice->currency = $currency;
                $invoice->payable_id = $student->id;
                $invoice->payable_type = 'student';
                $invoice->fee_type_id = $feeType->id;
                $invoice->applicable_id = $study_academic_year->id;
                $invoice->applicable_type = 'academic_year';
                $invoice->save();


                $generated_by = 'SP';
                $approved_by = 'SP';
                $inst_id = config('constants.SUBSPCODE');

                $result = $this->requestControlNumber($request,
                                            $invoice->reference_no,
                                            $inst_id,
                                            $invoice->amount,
                                            $feeType->description,
                                            $feeType->gfs_code,
                                            $feeType->payment_option,
                                            $student->id,
                                            $student->first_name.' '.$student->surname,
                                            $student->phone,
                                            $email,
                                            $generated_by,
                                            $approved_by,
                                            $feeType->duration,
                                            $invoice->currency);

            } 
        }elseif($request->get('fee_type') == 'LOST ID'){
            $identity_card_fee = FeeAmount::whereHas('feeItem',function($query){
                  $query->where('name','LIKE','%Identity Card%');
               })->where('study_academic_year_id',$study_academic_year->id)->first();


            if(str_contains($student->applicant->nationality,'Tanzania')){
              $amount = round($identity_card_fee->amount_in_tzs);
              $currency = 'TZS';
            }else{
              $amount = round($identity_card_fee->amount_in_usd*$usd_currency->factor);
              $currency = 'TZS';//'USD';
            }

            $feeType = FeeType::where('name','LIKE','%Identity Card%')->first();

            if(!$feeType){
                return redirect()->back()->with('error','Identity card fee type has not been set');
            }

            if($amount != 0.00){
                $invoice = new Invoice;
                $invoice->reference_no = 'MNMA-ID'.time();
                $invoice->actual_amount = $amount;
                $invoice->amount = $amount;
                $invoice->currency = $currency;
                $invoice->payable_id = $student->id;
                $invoice->payable_type = 'student';
                $invoice->fee_type_id = $feeType->id;
                $invoice->applicable_id = $study_academic_year->id;
                $invoice->applicable_type = 'academic_year';
                $invoice->save();

                $id_req = new IdCardRequest;
                $id_req->student_id = $student->id;
                $id_req->study_academic_year_id = $study_academic_year->id;
                $id_req->save();


                $generated_by = 'SP';
                $approved_by = 'SP';
                $inst_id = config('constants.SUBSPCODE');

                $result = $this->requestControlNumber($request,
                                            $invoice->reference_no,
                                            $inst_id,
                                            $invoice->amount,
                                            $feeType->description,
                                            $feeType->gfs_code,
                                            $feeType->payment_option,
                                            $student->id,
                                            $student->first_name.' '.$student->surname,
                                            $student->phone,
                                            $email,
                                            $generated_by,
                                            $approved_by,
                                            $feeType->duration,
                                            $invoice->currency);

            } 
        }
        DB::commit();

        return redirect()->back()->with('message','Control numbers requested successfully');
    }

    /**
     * Request control number
     */
    public function requestControlNumber(Request $request,$billno,$inst_id,$amount,$description,$gfs_code,$payment_option,$payerid,$payer_name,$payer_cell,$payer_email,$generated_by,$approved_by,$days,$currency){
      $data = array(
        'payment_ref'=>$billno,
        'sub_sp_code'=>$inst_id,
        'amount'=> $amount,
        'desc'=> $description,
        'gfs_code'=> $gfs_code,
        'payment_type'=> $payment_option,
        'payerid'=> $payerid,
        'payer_name'=> $payer_name,
        'payer_cell'=> $payer_cell,
        'payer_email'=> $payer_email,
        'days_expires_after'=> $days,
        'generated_by'=>$generated_by,
        'approved_by'=>$approved_by,
        'currency'=>$currency
      );

      //$txt=print_r($data, true);
      //$myfile = file_put_contents('/var/public_html/ifm/logs/req_bill.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            $url = url('bills/post_bill');
      $result = Http::withHeaders([
                        'X-CSRF-TOKEN'=> csrf_token()
                ])->post($url,$data);

      
    return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
            
    }

    /**
     * Show bank information
     */
    public function showBankInfo(Request $request)
    {
        $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
        $loan_allocation = LoanAllocation::where('index_number',$student->applicant->index_number)->first();
        $data = [
            'student'=>$student,
            'loan_allocation'=>$loan_allocation
        ];
        return view('dashboard.student.bank-information',$data)->withTitle('Bank Information');
    }

    /**
     * Update bank information
     */
    public function updateBankInfo(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'bank_name'=>'required',
            'account_number'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $student = Student::find($request->get('student_id'));
        $student->bank_name = $request->get('bank_name');
        $student->account_number = $request->get('account_number');
        $student->save();

        return redirect()->back()->with('message','Bank information updated successfully');
    }

    /**
     * Show loan allocations
     */
    public function showLoanAllocations(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $data = [
           'student'=>$student,
           'loan_allocations'=>LoanAllocation::with(['studyAcademicYear.academicYear'])->where('registration_number',$student->registration_number)->paginate(20)
        ];
        return view('dashboard.student.loan-allocations',$data)->withTitle('Loan Allocations');
    }

    /**
     * Show postponements
     */
    public function requestPostponement(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $data = [
           'study_academic_year'=>StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first(),
           'semester'=>Semester::where('status','ACTIVE')->first(),
           'student'=>$student,
           'postponements'=>Postponement::where('student_id',$student->id)->latest()->paginate(20)
        ];
        return view('dashboard.student.postponements',$data)->withTitle('Postponements');

    }
	
	/**
	 * Show indicate continue
	 */
	 public function showIndicateContinue(Request $request)
	 {
		 $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
		 
		 if($student->continue_status == 1){
		 $applicant = Applicant::where('index_number',$student->applicant->index_number)->with(['selections.campusProgram.program','selections'=>function($query){
                $query->orderBy('order','asc');
            },'nectaResultDetails'=>function($query){
                 $query->where('verified',1);
            },'nacteResultDetails'=>function($query){
                 $query->where('verified',1);
            },'outResultDetails'=>function($query){
                 $query->where('verified',1);
            },'selections.campusProgram.campus','nectaResultDetails.results','nacteResultDetails.results','outResultDetails.results','programLevel','applicationWindow'])->where('campus_id',$student->applicant->campus_id)->latest()->first();

        $window = $applicant->applicationWindow;

        $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                   $query->where('award_id',$applicant->program_level_id);
           })->with(['program','campus','entryRequirements'=>function($query) use($window){
                $query->where('application_window_id',$window->id);
           }])->where('campus_id',$applicant->campus_id)->get() : [];
        
        $award = $applicant->programLevel;
        $programs = [];

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $selected_program = array();
        
           $index_number = $applicant->index_number;
           $exam_year = explode('/', $index_number)[2];
          
           foreach($applicant->nectaResultDetails as $detail) {
              if($detail->exam_id == 2){
                  $index_number = $detail->index_number;
                  $exam_year = explode('/', $index_number)[2];
              }
           }

           if($exam_year < 2014 || $exam_year > 2015){
             $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
             $diploma_principle_pass_grade = 'E';
             $diploma_subsidiary_pass_grade = 'S';
             $principle_pass_grade = 'D';
             $subsidiary_pass_grade = 'S';
           }else{
             $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
             $diploma_principle_pass_grade = 'D';
             $diploma_subsidiary_pass_grade = 'E';
             $principle_pass_grade = 'C';
             $subsidiary_pass_grade = 'E';
           }
           // $selected_program[$applicant->id] = false;
           $subject_count = 0;
              foreach($campus_programs as $program){
                

                  if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                  }

                  // if($program->entryRequirements[0]->max_capacity == null){
                  //   return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
                  // }

                    if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
                       $o_level_must_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           $other_must_subject_ready = false;
                           foreach ($detail->results as $key => $result) {
                              
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                         $o_level_must_pass_count += 1;
                                       }
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }
                                      
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                          $o_level_must_pass_count += 1;
                                       }
                                       
                                       if(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                       if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) && !in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                    $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }
                         if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                             if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){
                               $programs[] = $program;
                             }
                         }else{
                            if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                               $programs[] = $program;
                             }
                         }
                         
                       }
                   }

                   // Diploma
                   if(str_contains($award->name,'Diploma')){
                       $o_level_pass_count = 0;
                       $o_level_must_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_subsidiary_pass_count = 0;
                       $diploma_major_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           $other_must_subject_ready = false;
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;


                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                         $o_level_must_pass_count += 1;
                                       }
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }
                                 
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                        $o_level_must_pass_count += 1;
                                       }
                                       
                                       if(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                       if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) && !in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                     $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id === 2){
                           $other_advance_must_subject_ready = false;
                           $other_advance_subsidiary_ready = false;
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_principle_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }else{
                                    $a_level_principle_pass_count += 1;
                                 }
                              }
                              if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                 }else{
                                    $a_level_subsidiary_pass_count += 1;
                                 }
                              }
                           }
                         }
                         
                       }
                       if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                       if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1) && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){
                           $programs[] = $program;
                        }
                        }else{
                            if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){
                             $programs[] = $program;
                           }
                        }
                       $has_btc = false;
                      

                       if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                           foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                                foreach($applicant->nacteResultDetails as $det){
                                   if(str_contains($det->programme,$sub) && str_contains($det->programme,'Basic')){
                                     $has_btc = true;
                                   }
                                }
                           }
                       }
                           

                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){
                           $programs[] = $program;
                       }
                   }
                   
                   // Bachelor
                   if(str_contains($award->name,'Bachelor')){
                       $o_level_pass_count = 0;
                       $o_level_must_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_principle_pass_points = 0;
                       $a_level_subsidiary_pass_count = 0;
                       $a_level_out_principle_pass_count = 0;
                       $a_level_out_principle_pass_points = 0;
                       $a_level_out_subsidiary_pass_count = 0;
                       $diploma_pass_count = 0;
                       
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           $other_must_subject_ready = false;
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                 $applicant->rank_points += $o_level_grades[$result->grade];
                                 $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                         $o_level_must_pass_count += 1;
                                       }
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }
    
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                          $o_level_must_pass_count += 1;
                                       }

                                       if(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                       if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) && !in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                      $o_level_pass_count += 1;
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           $other_advance_must_subject_ready = false;
                           $other_advance_subsidiary_ready = false;
                           $other_out_advance_must_subject_ready = false;
                           $other_out_advance_subsidiary_ready = false;
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades[$principle_pass_grade]){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_principle_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    }
                                 }else{
                                     $a_level_principle_pass_count += 1;
                                     $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                 }
                              }
                              if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                 }
                              }

                              if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_out_principle_pass_count += 1;
                                         $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                         $a_level_out_principle_pass_count += 1;
                                         $other_out_advance_must_subject_ready = true;
                                         $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_out_principle_pass_count += 1;
                                         $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_out_principle_pass_count += 1;
                                         $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                    }
                                 }else{
                                     $a_level_out_principle_pass_count += 1;
                                     $a_level_out_principle_pass_points += $a_level_grades[$result->grade];
                                 }
                              }
                              if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_subsidiary_pass_grade]){

                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_out_subsidiary_pass_count += 1;
                                       }
                                 }
                              }
                           }
                         }
                       }
                       
                       if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                       if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){

                           $programs[] = $program;
                       }
                       }else{
                           if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                           $programs[] = $program;
                       }
                       }
                       // foreach ($applicant->nacteResultDetails as $detailKey=>$detail) {
                       //   foreach ($detail->results as $key => $result) {
                       //        if($diploma_grades[$result->grade] >= $diploma_grades[$program->entryRequirements[0]->equivalent_average_grade]){
                       //           $diploma_pass_count += 1;
                       //        }
                       //     }
                       //  }

                       $has_major = false;
                       $equivalent_must_subjects_count = 0;
                       $nacte_gpa = null;
                       $out_gpa = null;

                       if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                           foreach($applicant->nacteResultDetails as $detail){
                             foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){

                               if(str_contains($detail->programme,$sub)){
                                   $has_major = true;
                               }
                             }
                             $nacte_gpa = $detail->diploma_gpa;
                           }
                       }else{
                          if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                              foreach($applicant->nacteResultDetails as $detail){
                                  foreach($detail->results as $result){
                                      foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                          if(str_contains($result->subject,$sub)){
                                              $equivalent_must_subjects_count += 1;
                                          }
                                      }
                                  }
                                  $nacte_gpa = $detail->diploma_gpa;
                              }
                          }
                       }
                        if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                            if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_major && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){
                                
                               $programs[] = $program;
                            }
                        }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                            if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)){
                                
                               $programs[] = $program;
                            }
                        }


                        $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects); //['OFC 017','OFP 018','OFP 020'];
                        $out_pass_subjects_count = 0;
                        
                        foreach($applicant->outResultDetails as $detail){
                            foreach($detail->results as $key => $result){
                                if(!in_array($result->code, $exclude_out_subjects_codes)){
                                   if($out_grades[$result->grade] >= $out_grades['C']){
                                      $out_pass_subjects_count += 1;
                                   }
                                }
                            }
                            $out_gpa = $detail->gpa;
                      
                        }


                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 && $a_level_out_principle_pass_count >= 1){
                                $programs[] = $program;
                        }
                            
                        if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                            if(($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){
                                    $programs[] = $program;
                            }
                        }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                            if($out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){
                                    $programs[] = $program;
                            }
                        }

                        if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){
                              $programs[] = $program;
                        }
                }
            if($subject_count != 0){
			   $app = Applicant::find($applicant->id);
               $app->rank_points = $applicant->rank_points / $subject_count;
			   $app->save();
            }
            
        }
	    }else{
			$window = null;
			$programs = [];
			$applicant = null;
		}
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find($student->campus_id),
           'application_window'=>$window,
           'campus_programs'=>$window? $programs : [],
           'campuses'=>Campus::all(),
		   'student'=>$student,
           'program_fee_invoice'=>Invoice::where('payable_id',$student->id)->where('payable_type','student')->first(),
           'request'=>$request
        ];
		 return view('dashboard.student.indicate-continue',$data)->withTitle('Indicate Continue');
	 }
	 
	 /**
	  * Indicate continue
	  */
	  public function indicateContinue(Request $request)
	  {
		  DB::beginTransaction();
		  $student = Student::has('overallRemark')->with(['applicant.programLevel','campusProgram.program','overallRemark'])->find($request->get('student_id'));
		  if($student->continue_status == 1){
			  return redirect()->back()->with('error','You have already indicated your continuation status');
		  }
		  if(!$student){
			  return redirect()->back()->with('error','You cannot indicate to continue with upper level');
		  }
		  $application_window = ApplicationWindow::where('campus_id',$student->applicant->campus_id)->where('status','ACTIVE')->latest()->first();
		  if(!$application_window){
			  return redirect()->back()->with('error','Application window not defined');
		  }

                 
		  
		  $past_level = $student->applicant->programLevel;
		  if(str_contains($past_level->code,'BTC')){
			  $level = Award::where('code','OD')->first();
		  }elseif(str_contains($past_level->code,'OD')){
			  $level = Award::where('code','HD')->first();
		  }elseif(str_contains($past_level->code,'HD')){
			  $level = Award::where('code','BD')->first();
		  }elseif(str_contains($past_level->code,'BD')){
			  $level = Award::where('code','MD')->first();
		  }

                  $window = $application_window;

                  $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($level){
                   $query->where('award_id',$level->id);
                  })->with(['program','campus','entryRequirements'=>function($query) use($window){
                 $query->where('application_window_id',$window->id);
                  }])->where('campus_id',$request->get('campus_id'))->get() : [];

                  if(count($campus_programs) == 0){
			  return redirect()->back()->with('error','Selected campus does not have programmes');
		  }
		  
		  $applicant = new Applicant;
		  $applicant->first_name = $student->applicant->first_name;
		  $applicant->middle_name = $student->applicant->middle_name;
		  $applicant->surname = $student->applicant->surname;
		  $applicant->index_number = $student->applicant->index_number;
		  $applicant->entry_mode = 'EQUIVALENT';
		  $applicant->campus_id = $student->applicant->campus_id;
		  $applicant->intake_id = $student->applicant->intake_id;
		  $applicant->application_window_id = $application_window->id;
		  $applicant->birth_date = $student->applicant->birth_date;
		  $applicant->nationality = $student->applicant->nationality;
		  $applicant->next_of_kin_id = $student->applicant->next_of_kin_id;
		  $applicant->email = $student->applicant->email;
		  $applicant->country_id = $student->applicant->country_id;
		  $applicant->region_id = $student->applicant->region_id;
		  $applicant->district_id = $student->applicant->district_id;
		  $applicant->ward_id = $student->applicant->ward_id;
		  $applicant->street = $student->applicant->street;
		  $applicant->nin = $student->applicant->nin;
		  $applicant->phone = $student->applicant->phone;
		  $applicant->gender = $student->applicant->gender;
		  $applicant->address = $student->applicant->address;
                  $applicant->o_level_certificate = $student->applicant->o_level_certificate;
                  $applicant->birth_certificate = $student->applicant->birth_certificate;
                  $applicant->a_level_certificate = $student->applicant->a_level_certificate;
                  $applicant->diploma_certificate = $student->applicant->diploma_certificate;
                  $applicant->passport_picture = $student->applicant->passport_picture;
                  $applicant->teacher_diploma_certificate = $student->applicant->teacher_diploma_certificate;
		  $applicant->disability_status_id = $student->applicant->disability_status_id;
		  $applicant->documents_complete_status = 1;
		  $applicant->program_level_id = $level->id;
		  $applicant->is_continue = 1;
		  
		  
		  
		  $user = new User;
		  $user->username = $applicant->index_number;
		  $user->password = Hash::make('123456');
		  $user->save();
		  
		  //$old_user = User::find($student->user_id);
		  //$old_user->status = 'INACTIVE';
		  //$old_user->save();
		  
		  $role = Role::where('name','applicant')->first();
		  $user->roles()->sync([$role->id]);
		  
		  $applicant->user_id = $user->id;
		  $applicant->save();
		  
		  NectaResultDetail::where('applicant_id',$student->applicant_id)->update(['applicant_id'=>$applicant->id]);
		  NectaResult::where('applicant_id',$student->applicant_id)->update(['applicant_id'=>$applicant->id]);

	      $results = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($student){
		        $query->where('year_of_study',$student->year_of_study);
	      })->where('student_id',$student->id)->get();
		  
		    $detail = new NacteResultDetail;
		    $detail->institution = 'MNMA';
			$detail->programme = $student->campusProgram->program->name;
			$detail->firstname = $student->first_name;
			$detail->middlename = $student->middle_name;
			$detail->surname = $student->surname;
			$detail->gender = $student->gender;
			$detail->avn = $student->registration_number;
			$detail->registration_number = $student->registration_number;
			$detail->diploma_gpa = $student->overallRemark->gpa;
			$detail->diploma_code = $student->campusProgram->program->code;
			$detail->diploma_category = 'Category';
			$detail->diploma_graduation_year = date('Y');
			$detail->username = $student->surname;
			$detail->date_birth = $student->birth_date;
			$detail->applicant_id = $applicant->id;
			$detail->verified = 1;
			$detail->save();
			
			
			foreach($results as $result){
				 $res = new NacteResult;
				 $res->subject = $result->moduleAssignment->module->name;
				 $res->grade = $result->grade;
				 $res->applicant_id = $applicant->id;
				 $res->nacte_result_detail_id = $detail->id;
				 $res->save();
			}
		  Student::where('id',$student->id)->update(['continue_status'=>1]);
		  DB::commit();
		  return redirect()->to('student/show-indicate-continue?campus_id='.$request->get('campus_id'))->with('message','You have indicated to continue with upper level successfully');
	  }

    /**
     * Update details
     */
    public function updateDetails()
    {
        $student = Student::find($request->get('student_id'));
        $student->studentship_status_id = $request->get('studentship_status_id');
        $student->save();

        return redirect()->back()->with('message','Student details updated successfully');
    }

    /**
     * Display modules
     */
    public function searchForStudent(Request $request)
    {
        $data = [
            'student'=>Student::with(['applicant.country','applicant.district','applicant.ward','campusProgram.campus','disabilityStatus'])->where('registration_number',$request->get('registration_number'))->first(),
            'statuses'=>StudentshipStatus::all()
        ];
        return view('dashboard.academic.student-search',$data)->withTitle('Student Search');
    }

    /**
     * Display modules
     */
    public function showStudentProfile(Request $request)
    {
        $data = [
            'student'=>Student::with(['applicant.country','applicant.district','applicant.ward','campusProgram.campus','disabilityStatus'])->where('registration_number',$request->get('registration_number'))->first(),
            'statuses'=>StudentshipStatus::all()
        ];
        return view('dashboard.academic.student-profile',$data)->withTitle('Profile');
    }

    /**
     * Set deceased
     */
    public function setDeceased(Request $request)
    {
        $status = StudentshipStatus::where('name','DECEASED')->first();
        $student = Student::find($request->get('student_id'));
        $student->studentship_status_id = $status->id;
        $student->save();

        $user = User::find($student->user_id);
        $user->status = 'INACTIVE';
        $user->save();

        return redirect()->back()->with('message','Studentship status updated successfully');
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $student = Student::find($request->get('student_id'));
        $user = User::find($student->user_id);
        $user->password = Hash::make('123456');
        $user->save();

        return redirect()->back()->with('message','Password reset successfully');
    }
         
     /**
     * Reset control number
     */
    public function resetControlNumber(Request $request)
    {
        $student = Student::find($request->get('student_id'));
        $invoice = Invoice::where('payable_id',$student->id)->where('payable_type','student')->latest()->first();
         if(GatewayPayment::where('control_no',$invoice->control_no)->count() == 0){
           $invoice->payable_id = 0;
           $invoice->save();
         }

        return redirect()->back()->with('message','Control number reset successfully');
    }

    /**
     * Special case students
     */
    public function specialCaseStudents(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
            'students'=>Student::whereHas('studentshipStatus',function($query){
                $query->where('name','DECEASED')->orWhere('name','POSTPONED');
            })->with(['postponements'=>function($query){
                $query->where('status','POSTPONED')->latest();
            },'studentshipStatus'])->latest()->get(),
            'staff'=>$staff
        ];
        return view('dashboard.academic.special-case-students',$data)->withTitle('Special Case Students');
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
