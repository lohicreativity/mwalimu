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
use App\Domain\Academic\Models\TranscriptRequest;
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
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\Applicant;
use App\Domain\Settings\Models\Currency;
use App\Domain\Academic\Models\Graduant;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\SpecialDate;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Registration\Actions\StudentAction;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;
use App\Utils\Util;
use Auth, Hash, Validator, DB;
use App\Domain\Academic\Models\SpecialExam;

class StudentController extends Controller
{
	/**
	 * Display student dashboard
	 */
	public function index()
	{
    $student = User::find(Auth::user()->id)->student()->with('applicant:id,campus_id,index_number,admission_confirmation_status')->first();
    $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
    $activeSemester = Semester::where('status', 'ACTIVE')->first();
	/* return Student::whereHas('TranscriptRequest', function($query) use($student)
		{$query->where('student_id', $student->id);})->latest()->first()->get(); */

    if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){

      if($activeSemester->id == 2){
        $semester_id = 1;
      }else{
        $semester_id = $activeSemester->id;
      }
      $ac_yr_id = $ac_year->id + 1;
    }else{

      $semester_id = $activeSemester->id;
      $ac_yr_id = $ac_year->id;
    }

    $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

    $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                ->where('campus_id',$student->applicant->campus_id)
                                ->where('study_academic_year_id',$ac_year->academicYear->id)
                                ->count();

		$data = [
			'study_academic_year'=>$ac_year,
      'student'=>$student,
      'loan_allocation'=>LoanAllocation::where('index_number',$student->applicant->index_number)->where('loan_amount','!=',0.00)->where('study_academic_year_id',$ac_yr_id)->where('study_academic_year_id',$ac_year->academicYear->id)->where('campus_id',$student->applicant->campus_id)->first(),
      'registration'=>Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester_id)->where('status','REGISTERED')->first(),
      'performance_report'=>PerformanceReportRequest::where('student_id',$student->id)->where('status','ATTENDED')->latest()->first(),
			'transcript_request_status'=> TranscriptRequest::select('status','updated_at')->where('student_id', $student->id)->where('status', 'ISSUED')->latest()->first(),
			'clearance_status'=>Clearance::where('student_id', $student->id)->latest()->first(),
      'loan_status'=>$loan_status
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
          $ac_year = StudyAcademicYear::where('status','ACTIVE')->with('academicYear')->first();
          $activeSemester = Semester::where('status', 'ACTIVE')->first();

          $student = Student::select('id','applicant_id','campus_program_id','year_of_study','academic_status_id','nationality','registration_number')
                            ->where('registration_number',$request->get('registration_number'))
                            ->with(['applicant:id,campus_id,intake_id','applicant.intake','academicStatus:id,name'])
                            ->first();

          if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){

            if($activeSemester->id == 2){
              $semester_id = 1;
            }else{
              $semester_id = $activeSemester->id;
            }
            $ac_yr_id = $ac_year->id + 1;
          }else{

            $semester_id = $activeSemester->id;
            $ac_yr_id = $ac_year->id;
          }

          $tuition_fee_loan = LoanAllocation::where(function($query) use($student){$query->where('applicant_id',$student->applicant_id)->orWhere('student_id',$student->id);})
                                            ->where('study_academic_year_id',$ac_yr_id)
                                            ->where('campus_id',$student->applicant->campus_id)
                                            ->sum('tuition_fee');

          $invoices = null;
          if(Registration::where('student_id', $student->id)
                         ->where('study_academic_year_id', $ac_yr_id)
                         ->where('semester_id', $semester_id)
                         ->where('status','UNREGISTERED')
                         ->first()){

            $program_fee = ProgramFee::where('study_academic_year_id',$ac_yr_id)
                                     ->where('campus_program_id',$student->campus_program_id)
                                     ->first();
            if(!$program_fee){
                return redirect()->back()->with('error','Programme fee has not been defined. Please contact the Admission Office.');
            }

            $usd_currency = Currency::where('code','USD')->first();

            if(str_contains($student->nationality,'Tanzania')){
                $program_fee_amount = $program_fee->amount_in_tzs;
            }else{
                $program_fee_amount = round($program_fee->amount_in_usd * $usd_currency->factor);
            }

            $loan_signed_status = LoanAllocation::where(function($query) use($student){$query->where('applicant_id',$student->applicant_id)->orWhere('student_id',$student->id);})
                                                ->where('study_academic_year_id',$ac_yr_id)
                                                ->where('campus_id',$student->applicant->campus_id)
                                                ->where('has_signed',1)
                                                ->count();
            if($student->academic_status_id == 8){
              $loan = LoanAllocation::where('applicant_id',$student->applicant_id)
                                    ->where('study_academic_year_id',$ac_yr_id)
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->first(); 
              if($loan && empty($loan->registration_number)){
                $loan->registration_number = $request->get('registration_number');
                $loan->save();
              }
            }
                                               
            if($tuition_fee_loan >= $program_fee_amount && $loan_signed_status >= 1){
              Registration::where('student_id',$student->id)
                          ->where('study_academic_year_id',$ac_yr_id)
                          ->where('semester_id', $semester_id)
                          ->update(['status'=>'REGISTERED']);

            }else{
              $invoices = Invoice::where('payable_type','student')
                                 ->where('payable_id',$student->id)
                                 ->whereNotNull('gateway_payment_id')
                                 ->where('applicable_id',$ac_yr_id)
                                 ->with('feeType')
                                 ->get();

              if($invoices){
                $fee_payment_percent = $other_fee_payment_status = 0;
                foreach($invoices as $invoice){
                  if(str_contains($invoice->feeType->name,'Tuition Fee')){
                    $paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
                    $fee_payment_percent = $paid_amount/$invoice->amount;

                    if($tuition_fee_loan>0){
                      $fee_payment_percent = ($paid_amount+$tuition_fee_loan)/$program_fee_amount;
                    }
                    break;
                  }
                }

                if($student->year_of_study == 1 && $student->academicStatus->name == 'FRESHER'){
                  $other_fee_payment_status = false;
                  foreach($invoices as $invoice){
                    if(str_contains($invoice->feeType->name,'Miscellaneous Income')){
                      $other_fee_payment_status = true;
                      break;
                    }
                  }

                  if($semester_id == 1){
                    if($fee_payment_percent >= 0.6 && $other_fee_payment_status){
                      if($tuition_fee_loan > 0){
                        if($loan_signed_status >= 1){
                          Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)
                          ->where('semester_id', $semester_id)->update(['status'=>'REGISTERED']);
                        }
                      }else{
                        Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)
                        ->where('semester_id', $semester_id)->update(['status'=>'REGISTERED']);
                      }
      
                    }

                  }elseif($semester_id == 2){
                    if($fee_payment_percent == 1 && $other_fee_payment_status){
                      Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)
                      ->where('semester_id', $semester_id)->update(['status'=>'REGISTERED']);
      
                    }
                  }
                }else{
                  if($semester_id == 1){
                    if($fee_payment_percent >= 0.6){
                      Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)
                      ->where('semester_id', $semester_id)->update(['status'=>'REGISTERED']);
      
                    }
                  }elseif($semester_id == 2){
                    if($fee_payment_percent == 1){
                      Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)
                      ->where('semester_id', $semester_id)->update(['status'=>'REGISTERED']);
      
                    }
                  }
                }
              }
            }
          }

          // if(Auth::user()->must_update_password == 1){
          //     return redirect()->to('change-password')->with('error','You must change the default password');
          // }else{
          return redirect()->to('student/dashboard')->with('message','Logged in successfully');
          //}
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
        },'academicStatus','applicant.intake:id,name'])->first();
    	$campus = CampusProgram::find($student->campus_program_id)->campus;
    	$program = CampusProgram::find($student->campus_program_id)->program;
      
    	$study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){
                $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
            },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','moduleAssignments.moduleAssignments.staff','academicYear'])->where('status','ACTIVE')->first();
    	if(!$study_academic_year){
    		return redirect()->back()->with('error','No active academic year');
    	}

      $activeSemester = Semester::select('id')->where('status', 'ACTIVE')->first();
      if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){

        if($activeSemester->id == 2){
          $semester_id = 1;
        }else{
          $semester_id = $activeSemester->id;
        }
        $ac_yr_id = $study_academic_year->id + 1;
      }else{
  
        $semester_id = $activeSemester->id;
        $ac_yr_id = $study_academic_year->id;
      }

      $ac_year = StudyAcademicYear::with(['academicYear','moduleAssignments'=>function($query) use($student){
        $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);
    },'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','moduleAssignments.moduleAssignments.staff','academicYear'])->where('id',$ac_yr_id)->first();
      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                  ->where('campus_id',$student->applicant->campus_id)
                                  ->where('study_academic_year_id',$ac_year->academicYear->id)
                                  ->count();

    	$data = [
        'student'=>$student,
        'study_academic_year'=>StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){$query->where('campus_program_id',$student->campus_program_id)
                                                                                                                  ->where('year_of_study',$student->year_of_study);},
                                                        'moduleAssignments.campusProgram',
                                                        'moduleAssignments.module',
                                                        'moduleAssignments.semester',
                                                        'moduleAssignments.moduleAssignments.staff',
                                                        'academicYear'])
                                                ->where('id',$ac_yr_id)
                                                ->first(),
        'semesters'=>Semester::with(['electivePolicies'=>function($query) use ($student,$ac_yr_id){$query->where('campus_program_id',$student->campus_program_id)
                                                                                                                    ->where('study_academic_year_id',$ac_yr_id);},
                                      'electiveDeadlines'=>function($query) use ($ac_yr_id,$campus,$program){$query->where('campus_id',$campus->id)
                                                                                                                              ->where('study_academic_year_id',$ac_yr_id)
                                                                                                                              ->where('award_id',$program->award_id);}])
                              ->where('status', 'ACTIVE')->get(),
        'options'=>Student::find($student->id)->options,
        'active_semester'=>Semester::select('id')->where('id', $semester_id)->get(),
        'loan_status'=>$loan_status
    	];

    	return view('dashboard.student.modules',$data)->withTitle('Modules');
    }

    /**
     * Display modules
     */
    public function showPayments(Request $request)
    {
      $student = User::find(Auth::user()->id)->student()->with(['applicant:id,intake_id,campus_id','applicant.intake:id,name','registrations'=> function($query){$query->latest()->first();}])->first();

      $payments = Invoice::where(function($query) use($student){$query->where(function($query) use($student){$query->where('payable_id',$student->id)->where('payable_type','student');})
                                                        ->orWhere(function($query) use($student){$query->where('payable_id',$student->applicant_id)->where('payable_type','applicant');});})
                          ->with('feeType','gatewayPayment')->whereNotNull('gateway_payment_id')->latest()->get();

      $total_fee_paid_amount = 0;
      foreach($payments as $payment){
        if(str_contains($payment->feeType->name, 'Tuition')){
            $total_fee_paid_amount = GatewayPayment::where('bill_id', $payment->reference_no)->sum('paid_amount');
            break;
        }
      }
      
      $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();

      if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){
        $ac_yr_id = $ac_year->id + 1;
      }else{
        $ac_yr_id = $ac_year->id;
      }

      $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();
      $tuition_fee_loan = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                        ->where('year_of_study',$student->year_of_study)
                                        ->where('study_academic_year_id',$ac_year->academicYear->id)
                                        ->where('campus_id',$student->applicant->campus_id)
                                        ->sum('tuition_fee');
      
      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                   ->where('year_of_study',$student->year_of_study)
                                   ->where('study_academic_year_id',$ac_year->academicYear->id)
                                   ->where('campus_id',$student->applicant->campus_id)
                                   ->count();
      
      $programme_fee = ProgramFee::select('amount_in_tzs')->where('study_academic_year_id',$ac_year->academicYear->id)->where('campus_program_id',$student->campus_program_id)->first();
     
      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                  ->where('campus_id',$student->applicant->campus_id)
                                  ->where('study_academic_year_id',$ac_year->academicYear->id)
                                  ->count();

      $data = [
        'study_academic_year'=>StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first(),
        'student'=>$student,
        'payments'=>$payments,
        'total_paid_fee'=>$total_fee_paid_amount,
        'tuition_fee_loan'=>$tuition_fee_loan,
        'programme_fee'=>$programme_fee->amount_in_tzs,
        'loan_status'=>$loan_status
      ];
    	return view('dashboard.student.payments',$data)->withTitle('Payments');
    }

    /**
     * Display modules
     */
    public function showProfile(Request $request)
    {
      $student = User::find(Auth::user()->id)->student()->with(['applicant.country','applicant.district','applicant.ward','campusProgram.campus','disabilityStatus'])->first();
      $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();

      if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){
        $ac_yr_id = $ac_year->id + 1;
      }else{
        $ac_yr_id = $ac_year->id;
      }

      $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();
      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                  ->where('campus_id',$student->applicant->campus_id)
                                  ->where('study_academic_year_id',$ac_year->academicYear->id)
                                  ->count();
    	$data = [
            'student'=>$student,
            'loan_status'=>$loan_status,
            'study_academic_year'=>$ac_year
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
            $study_academic_year = StudyAcademicYear::with(['moduleAssignments'=>function($query) use($student){$query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$student->year_of_study);},
                                                            'moduleAssignments.campusProgram','moduleAssignments.module','moduleAssignments.semester','academicYear'])
                                                    ->where('status','ACTIVE')
                                                    ->first();

           $elective_policy = ElectivePolicy::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)->where('campus_program_id',$assignment->campus_program_id)->first();

           $elective_module_limit = ElectiveModuleLimit::where('study_academic_year_id',$study_academic_year->id)->where('semester_id',$assignment->semester_id)
		   ->where('campus_id',$assignment->campusProgram->campus->id)->where('award_id', $assignment->campusProgram->program->award_id)->first();

           if($elective_module_limit){
           	   if(strtotime($elective_module_limit->deadline) < strtotime(now()->format('Y-m-d'))){
           	   	  return redirect()->back()->with('error','Options selection deadline already passed');
           	   }
           }
           $options = Student::find($student->id)->options()->where('semester_id',$assignment->semester_id)->get();

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
    	$student = User::find(Auth::user()->id)->student()->with('applicant:id,intake_id')->first();
    	$results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();
      $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
      
      if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){
        $ac_yr_id = $ac_year->id + 1;
      }else{
        $ac_yr_id = $ac_year->id;
      }

      $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();
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
      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                  ->where('campus_id',$student->applicant->campus_id)
                                  ->where('study_academic_year_id',$ac_year->academicYear->id)
                                  ->count();
    	$data = [
    	  'study_academic_year'=>$ac_year,
		    'years_of_studies'=>$years_of_studies,
        'results_present_status'=>count($results) != 0? true : false,
        'student'=>$student,
        'loan_status'=>$loan_status
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
         }])->with(['applicant:id,intake_id,campus_id','campusProgram.program'])->first();
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);

         $retake_sem_remarks = SemesterRemark::where('student_id',$student->id)->where('remark','RETAKE')->where('year_of_study',$yr_of_study)->get();

         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->get();

         if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }
  
        $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

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

          $publications = ResultPublication::where('campus_id',$student->applicant->campus_id)
                                           ->where('study_academic_year_id',$ac_yr_id)
                                           ->where('status','PUBLISHED')
                                           ->where('nta_level_id',$student->campusProgram->program->nta_level_id)
                                           ->get();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }
         $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                      ->where('campus_id',$student->applicant->campus_id)
                                      ->where('study_academic_year_id',$ac_year->academicYear->id)
                                      ->count();
        $special_exams = SpecialExam::where('student_id',$student->id)
                                    ->where('type','FINAL')
                                    ->where('study_academic_year_id',$ac_yr_id)
                                    ->where('status','APPROVED')
                                    ->get();
         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
          'retake_sem_remarks'=>$retake_sem_remarks,
         	'study_academic_year'=>$ac_year,
         	'core_programs'=>$core_programs,
         	'publications'=>$publications,
         	'optional_programs'=>$optional_programs,
          'student'=>$student,
          'loan_status'=>$loan_status,
          'special_exams'=>$special_exams
         ];
         return view('dashboard.student.examination-results-report',$data)->withTitle('Examination Results');
    }


    /**
     * Display student overall results
     */
    public function showStudentOverallResults(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
      if($student_id > 0 && !User::find(Auth::user()->id)->staff){
        return redirect()->back()->with('error','You cannot perform this action');
      }
        if($student_id > 0){
          $student = Student::with(['applicant:id,intake_id,campus_id','campusProgram.program'])->find($student_id);
        }else{
          $student = User::find(Auth::user()->id)->student()->with(['campusProgram.program'])->first();
          $student_id = $student->id;
        }
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study, $request){
           $query->where('student_id',$student->id)->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });
         }])->get();

         if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }
  
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

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
         },'moduleAssignment','moduleAssignment.module','carryHistory'=>function($query) use($study_academic_year){$query->where('study_academic_year_id',$study_academic_year->id - 1);
         },'carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory'=>function($query) use($study_academic_year){$query->where('study_academic_year_id',$study_academic_year->id - 1);},'retakeHistory.retakableResults'=>function($query){
          $query->latest();}])->where('student_id',$student->id)->get();

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

            $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->where('campus_id',$student->applicant->campus_id)->get();

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

        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();

        $special_exams = SpecialExam::where('student_id',$student->id)
                                    ->where('type','FINAL')
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->where('status','APPROVED')
                                    ->get();

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
          'loan_status'=>$loan_status,
          'special_exams'=>$special_exams
          
         ];
         return view('dashboard.student.examination-results-overall-report',$data)->withTitle('Student Overall Results');
    }

    /**
     * Show registration
     */
    public function showRegistration(Request $request)
    {
        $student = User::find(Auth::user()->id)->student()->with('applicant:id,campus_id','applicant.intake:id,name')->first();
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();

        if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }
  
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();

        if($student->applicant->intake->name == 'September'){
          $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->first();
        }else{
          $semester = Semester::where('status','ACTIVE')->first();
          $semester_id = session('active_semester_id');
          if($semester->id == 2){
            $semester_id = 1;
          }

          $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',session('active_academic_year_id')+1)->where('semester_id',$semester_id)->where('status','REGISTERED')->first();
        }

        $data = [
			    'study_academic_year'=>$study_academic_year,
          'student'=>$student,
          'registration'=>$registration,
          'loan_status'=>$loan_status,
        ];
        return view('dashboard.student.registration',$data)->withTitle('Registration');
    }

    /**
     * Request control number
     */
    public function showRequestControlNumber(Request $request)
    {
        $student = User::find(Auth::user()->id)->student;
        $student = Student::select('id','first_name','middle_name','surname','phone','email','nationality','applicant_id','registration_number','campus_program_id','studentship_status_id','academic_status_id','year_of_study')
                          ->where('id',$student->id)
                          ->with(['applicant:id,program_level_id,campus_id,intake_id','applicant.programLevel:id,name','campusProgram:id,program_id','campusProgram.program:id,name',
                                  'studentshipStatus:id,name'])->first();

        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();

        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }

        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

        if($student->year_of_study == 1 && $student->academic_status_id == 8){
          $other_fee_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','Miscellaneous Income');})
                                      ->where('payable_type','student')
                                      ->where('payable_id',$student->id)
                                      ->where('applicable_id',$study_academic_year->id)
                                      ->first();
          
          if(empty($other_fee_invoice)){
            if(str_contains(strtolower($student->applicant->programLevel->name),'bachelor')){
                $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                    $query->where('name','LIKE','%TCU%')->where('name','NOT LIKE','%Master%');
                })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->first();

                if(str_contains($student->campusProgram->program->name, 'Education')){
                    if($student->applicant->campus_id == 1){
                      
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){$query->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                                    ->where('name','NOT LIKE','%Master%')
                                ->where(function($query){$query->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Welfare Emergency%')
                                ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','%Medical Examination%');});})->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                    }else{
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');

                    }

                }else{
                    if($student->applicant->campus_id == 1){
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                    }else{
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');

                    }
                }
            }elseif(str_contains(strtolower($student->campusProgram->program->name), 'education')){

                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                        ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                        ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                        ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                        ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                
            }elseif(str_contains(strtolower($student->applicant->programLevel->name),'master')){

                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$student->applicant->campus_id)
                ->whereHas('feeItem',function($query) use($student){$query->where('campus_id',$student->applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%NACTVET%');})->first();

                if($student->applicant->campus_id == 1){
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                            ->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                            ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
        
                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                            ->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                            ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                }else{
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                            ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                            ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
        
                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                            ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                            ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                }

            }else{
                $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                    $query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
                })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->first();

                if($student->applicant->campus_id == 1){
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                }else{
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');

                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');

                }
            }
            $usd_currency = Currency::where('code','USD')->first();
            $other_fees_tzs = $other_fees_tzs + $quality_assurance_fee->amount_in_tzs;
            $other_fees_usd = $other_fees_usd + $quality_assurance_fee->amount_in_usd;
            if(str_contains($student->nationality,'Tanzania')){
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
            $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
            $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;
            $email = $student->email? $student->email : 'admission@mnma.ac.tz';

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
    
            $number_filter = preg_replace('/[^0-9]/','',$email);
            $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';

            return $this->requestControlNumber($request,
                                        $invoice->reference_no,
                                        $inst_id,
                                        $invoice->amount,
                                        $feeType->description,
                                        $feeType->gfs_code,
                                        $feeType->payment_option,
                                        $student->id,
                                        $first_name.' '.$surname,
                                        $student->phone,
                                        $payer_email,
                                        $generated_by,
                                        $approved_by,
                                        $feeType->duration,
                                        $invoice->currency);
          }
        }
        
        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();
        $data = [
			'study_academic_year'=>$study_academic_year,
           'fee_types'=>FeeType::all(),
           'student'=>$student,
           'invoices'=>Invoice::with(['applicable','feeType','gatewayPayment'])->where(function($query) use($student){
			      $query->where('payable_id',$student->id)->where('payable_type','student');
		   })->orWhere(function($query) use($student){
			      $query->where('payable_id',$student->applicant_id)->where('payable_type','applicant');
		   })->latest()->get(),
       'loan_status'=>$loan_status
        ];
		//return redirect()->back()->with('error','Some modules are missing final marks ('.implode(',', $missing_programs).')');
		//return $data['invoices'];
        return view('dashboard.student.request-control-number',$data)->withTItle('Request Control Number');
    }

    /**
     * Request control number
     */
    public function requestPaymentControlNumber(Request $request)
    {
        $student = Student::with(['applicant:id,program_level_id,intake_id,index_number,campus_id','applicant.programLevel:id,name','studentshipStatus:id,name','academicStatus:id,name','semesterRemarks'=>function($query){
            $query->latest();},'semesterRemarks.semester'])->find($request->get('student_id'));
        $email = $student->email? $student->email : 'admission@mnma.ac.tz';

        DB::beginTransaction();
        $study_academic_year = StudyAcademicYear::find(session('active_academic_year_id'));
        $semester = Semester::find(session('active_semester_id'));

        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){

          if($semester->id == 2){
            $semester_id = 1;
          }else{
            $semester_id = $semester->id;
          }
          $ac_yr_id = $study_academic_year->id + 1;
        }else{

          $semester_id = $semester->id;
          $ac_yr_id = $study_academic_year->id;
        }

        $study_academic_year = StudyAcademicYear::where('id',$ac_yr_id)->first();
        $semester = Semester::find($semester_id);
        $usd_currency = Currency::where('code','USD')->first();

        foreach($student->semesterRemarks as $rem){
          if($student->academicStatus->name == 'RETAKE'){
              if($rem->semester_id == $semester_id && $rem->remark != 'RETAKE'){
                DB::rollback();
                return redirect()->back()->with('error','You are not allowed to register for retake in this semester');
              }
          }
        }

        if($student->studentshipStatus->name == 'POSTPONED'){
          DB::rollback();
             return redirect()->back()->with('error','You cannot continue with registration because you have been postponed');
        }

        if($student->studentshipStatus->name == 'GRADUANT'){
          DB::rollback();
            return redirect()->back()->with('error','You cannot continue with registration because you have already graduated');
        }

        if($student->academicStatus->name == 'FAIL&DISCO'){
          DB::rollback();
          return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
        }

        if($student->academicStatus->name == 'ABSCOND'){
          return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }

        if($student->academicStatus->name == 'INCOMPLETE'){
          DB::rollback();
          return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }
        
        $annual_remarks = AnnualRemark::where('student_id',$student->id)->latest()->get();
        if(count($annual_remarks) != 0){
          $last_annual_remark = $annual_remarks[0];
          $year_of_study = $last_annual_remark->year_of_study;
          if($last_annual_remark->remark == 'REPEAT'){
                $year_of_study = $last_annual_remark->year_of_study;
          }elseif($last_annual_remark->remark == 'PASS' || $last_annual_remark->remark == 'RETAKE' || $last_annual_remark->remark == 'CARRY'){
            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
                    $year_of_study = $last_annual_remark->year_of_study + 1;
            }else{
                    $year_of_study = $last_annual_remark->year_of_study;
            }
          }
        }else{
          $year_of_study = 1;
        }

        // if(count($annual_remarks) != 0){
        //     $last_annual_remark = $annual_remarks[0];
        //     $year_of_study = $last_annual_remark->year_of_study;
        //     if($last_annual_remark->remark == 'RETAKE'){
        //         $year_of_study = $last_annual_remark->year_of_study;
        //     }elseif($last_annual_remark->remark == 'CARRY'){
        //         $year_of_study = $last_annual_remark->year_of_study;
        //     }elseif($last_annual_remark->remark == 'PASS'){
        //         if(str_contains($semester_remarks[0]->semester->name,'2')){
        //            $year_of_study = $last_annual_remark->year_of_study + 1;
        //         }else{
        //            $year_of_study = $last_annual_remark->year_of_study;
        //         }
        //     }elseif($last_annual_remark->remark == 'FAIL&DISCO'){
        //     $can_register = false;
        //     DB::rollback();
        //     return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
        //   }elseif($last_annual_remark->remark == 'INCOMPLETE'){
        //     $can_register = false;
        //     DB::rollback();
        //     return redirect()->back()->with('error','You cannot continue with registration because you have incomplete results');
        //   }
        // }

        if($request->get('fee_type') == 'TUITION'){
          $existing_tuition_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Tuition%');})
                                              ->where('applicable_type','academic_year')
                                              ->where('applicable_id',$study_academic_year->id)
                                              ->where('payable_id',$student->id)
                                              ->where('payable_type','student')
                                              ->first();

          if($existing_tuition_invoice){
            DB::rollback();
              return redirect()->back()->with('error','You have already requested for tuition fee control number in this academic year');
          }

          $program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)
                                   ->where('campus_program_id',$student->campus_program_id)
                                   ->where('year_of_study',$year_of_study)
                                   ->with('feeItem.feeType')
                                   ->first();

          if(!$program_fee){
            DB::rollback();
              return redirect()->back()->with('error','Programme fee has not been set');
          }

          $loan_allocation = LoanAllocation::where('index_number',$student->applicant->index_number)
                                           ->where('year_of_study',$year_of_study)
                                           ->where('study_academic_year_id',$study_academic_year->id)
                                           ->where('tuition_fee','>',0)
                                           ->where('campus_id',$student->applicant->campus_id)
                                           ->first();
          if($loan_allocation){
                if(str_contains($student->nationality,'Tanzania')){
                    $amount = $program_fee->amount_in_tzs - $loan_allocation->tuition_fee;

                }else{
                    $amount = round(($program_fee->amount_in_usd - $loan_allocation->tuition_fee/$usd_currency->factor) * $usd_currency->factor);

                }
          }else{
            if($student->academicStatus->name == 'RETAKE'){
              if(str_contains($student->nationality,'Tanzania')){
                $amount = round(0.5*$program_fee->amount_in_tzs);

              }else{
                $amount = round(0.5*$program_fee->amount_in_usd*$usd_currency->factor);

              }
            }else{
              if(str_contains($student->nationality,'Tanzania')){
                  $amount = round($program_fee->amount_in_tzs);

              }else{
                  $amount = round($program_fee->amount_in_usd*$usd_currency->factor);
              }
            }

          }

          if(str_contains($student->nationality,'Tanzania')){
              $amount_without_loan = round($program_fee->amount_in_tzs);
          }else{
              $amount_without_loan = round($program_fee->amount_in_usd*$usd_currency->factor);
          }


          if($amount != 0.00){
                $invoice = new Invoice;
                $invoice->reference_no = 'MNMA-TF-'.time();
                $invoice->actual_amount = $amount_without_loan;
                $invoice->amount = $amount;
                $invoice->currency = 'TZS';
                $invoice->payable_id = $student->id;
                $invoice->payable_type = 'student';
                $invoice->applicable_id = $study_academic_year->id;
                $invoice->applicable_type = 'academic_year';
                $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
                $invoice->save();


                $generated_by = 'SP';
                $approved_by = 'SP';
                $inst_id = config('constants.SUBSPCODE');

                $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
                $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

                $number_filter = preg_replace('/[^0-9]/','',$email);
                $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';
                $this->requestControlNumber($request,
                                            $invoice->reference_no,
                                            $inst_id,
                                            $invoice->amount,
                                            $program_fee->feeItem->feeType->description,
                                            $program_fee->feeItem->feeType->gfs_code,
                                            $program_fee->feeItem->feeType->payment_option,
                                            $student->id,
                                            $first_name.' '.$surname,
                                            $student->phone,
                                            $payer_email,
                                            $generated_by,
                                            $approved_by,
                                            $program_fee->feeItem->feeType->duration,
                                            $invoice->currency);
          }

          if($year_of_study == 1 && $student->academic_status_id == 8){
            $other_fee_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','Miscellaneous Income');})
                                        ->where('payable_type','student')
                                        ->where('payable_id',$student->id)
                                        ->where('applicable_id',$study_academic_year->id)
                                        ->first();
            
            if(empty($other_fee_invoice)){
              if(str_contains(strtolower($student->applicant->programLevel->name),'bachelor')){
                  $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                      $query->where('name','LIKE','%TCU%')->where('name','NOT LIKE','%Master%');
                  })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->first();
  
                  if(str_contains($student->campusProgram->program->name, 'Education')){
                      if($student->applicant->campus_id == 1){
                        
                          $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){$query->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                                      ->where('name','NOT LIKE','%Master%')
                                  ->where(function($query){$query->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Welfare Emergency%')
                                  ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','%Medical Examination%');});})->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                          $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                      }else{
                          $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                          $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
  
                      }
  
                  }else{
                      if($student->applicant->campus_id == 1){
                          $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                          $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                      }else{
                          $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                          $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                              $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                  ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                                  ->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                                  ->orWhere('name','LIKE','Student\'s Union%');
                              })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
  
                      }
                  }
              }elseif(str_contains(strtolower($student->campusProgram->program->name), 'education')){
  
                  $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                      $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                          ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                          ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
                  })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                  $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                      $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                          ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                          ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
                  })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                  
              }elseif(str_contains(strtolower($student->applicant->programLevel->name),'master')){
  
                  $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$student->applicant->campus_id)
                  ->whereHas('feeItem',function($query) use($student){$query->where('campus_id',$student->applicant->campus_id)
                  ->where('name','LIKE','%Master%')->where('name','LIKE','%NACTVET%');})->first();
  
                  if($student->applicant->campus_id == 1){
                      $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                              ->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                              ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
          
                      $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                              ->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                              ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                  }else{
                      $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                              ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                              ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
          
                      $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card%')
                              ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Welfare Emergency%')->orWhere('name','LIKE','%Caution Money%')
                              ->orWhere('name','LIKE','%Union%')->orWhere('name','LIKE','Examination Fee%');});
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                  }
  
              }else{
                  $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                      $query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
                  })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->first();
  
                  if($student->applicant->campus_id == 1){
                      $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                              ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                              ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                              ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                      $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                              ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                              ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                              ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
                  }else{
                      $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                              ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                              ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                              ->orWhere('name','LIKE','Student\'s Union%');
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_tzs');
  
                      $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                          $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                              ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card%')
                              ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergency%')
                              ->orWhere('name','LIKE','Student\'s Union%');
                      })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', $student->applicant->campus_id)->sum('amount_in_usd');
  
                  }
              }
              $usd_currency = Currency::where('code','USD')->first();
              $other_fees_tzs = $other_fees_tzs + $quality_assurance_fee->amount_in_tzs;
              $other_fees_usd = $other_fees_usd + $quality_assurance_fee->amount_in_usd;
              if(str_contains($student->nationality,'Tanzania')){
                  $other_fees = round($other_fees_tzs);
              }else{
                  $other_fees = round($other_fees_usd*$usd_currency->factor);
              }
  
              $feeType = FeeType::where('name','LIKE','%Miscellaneous%')->first();
  
              if(!$feeType){
                DB::rollback();
                  return redirect()->back()->with('error','Miscellaneous fee type has not been set');
              }
              $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
              $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;
              $email = $student->email? $student->email : 'admission@mnma.ac.tz';
  
              $invoice = new Invoice;
              $invoice->reference_no = 'MNMA-MSC'.time();
              $invoice->actual_amount = $other_fees;
              $invoice->amount = $other_fees;
              $invoice->currency = 'TZS';
              $invoice->payable_id = $student->id;
              $invoice->payable_type = 'student';
              $invoice->fee_type_id = $feeType->id;
              $invoice->applicable_id = $study_academic_year->id;
              $invoice->applicable_type = 'academic_year';
              $invoice->save();
      
              $generated_by = 'SP';
              $approved_by = 'SP';
              $inst_id = config('constants.SUBSPCODE');
      
              $number_filter = preg_replace('/[^0-9]/','',$email);
              $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';
  
              return $this->requestControlNumber($request,
                                          $invoice->reference_no,
                                          $inst_id,
                                          $invoice->amount,
                                          $feeType->description,
                                          $feeType->gfs_code,
                                          $feeType->payment_option,
                                          $student->id,
                                          $first_name.' '.$surname,
                                          $student->phone,
                                          $payer_email,
                                          $generated_by,
                                          $approved_by,
                                          $feeType->duration,
                                          $invoice->currency);
            }
          }
        }elseif($request->get('fee_type') == 'LOST ID'){

          $feeType = FeeType::where('name','LIKE','%Identity Card%')->first();

          if(!$feeType){
            DB::rollback();
            return redirect()->back()->with('error','Identity card fee type has not been set');
          }

          $unpaid_id_card = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Identity Card%');})
                                             ->where('applicable_type','academic_year')
                                             ->where('applicable_id',$study_academic_year->id)
                                             ->where('payable_id',$student->id)
                                             ->where('payable_type','student')
                                             ->whereNull('gateway_payment_id')
                                             ->first();

          if($unpaid_id_card){
            DB::rollback();
            return redirect()->back()->with('error','You have already requested for ID card control number in this academic year');
          }

          $annual_remark = AnnualRemark::where('student_id',$student->id)->count();
          if($year_of_study == 1 && $annual_remark == 0){
            $identity_card_fee = FeeAmount::whereHas('feeItem',function($query){$query->where('name','NOT LIKE','%Master%')->where('name','LIKE','%New%')->where('name','LIKE','%ID Card%');})
                                          ->where('study_academic_year_id',$study_academic_year->id)
                                          ->where('campus_id',$student->applicant->campus_id)
                                          ->first();

          }else{
            $identity_card_fee = FeeAmount::whereHas('feeItem',function($query){$query->where('name','NOT LIKE','%Master%')->where('name','LIKE','%Continue%')->where('name','LIKE','%ID Card%');})
                                          ->where('study_academic_year_id',$study_academic_year->id)
                                          ->where('campus_id',$student->applicant->campus_id)
                                          ->first();
          }

          if(!$identity_card_fee){
            DB::rollback();
            return redirect()->back()->with('error','ID card fee amount has not been set');
          }

          if(str_contains($student->nationality,'Tanzania')){
            $amount = round($identity_card_fee->amount_in_tzs);

          }else{
            $amount = round($identity_card_fee->amount_in_usd*$usd_currency->factor);
          }

          if($amount != 0.00){
            $invoice = new Invoice;
            $invoice->reference_no = 'MNMA-ID'.time();
            $invoice->actual_amount = $amount;
            $invoice->amount = $amount;
            $invoice->currency = 'TZS';
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

            $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
            $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

            $number_filter = preg_replace('/[^0-9]/','',$email);
            $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';
            $this->requestControlNumber($request,
                                        $invoice->reference_no,
                                        $inst_id,
                                        $invoice->amount,
                                        $feeType->description,
                                        $feeType->gfs_code,
                                        $feeType->payment_option,
                                        $student->id,
                                        $first_name.' '.$surname,
                                        $student->phone,
                                        $payer_email,
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
    public function requestControlNumber(Request $request,$billno,$inst_id,$amount,$description,$gfs_code,$payment_option,$payerid,$payer_name,$payer_cell,$payer_email,$generated_by,$approved_by,$days,$currency)
    {
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

      if(str_contains($billno,'MNMA-MSC')){
        return redirect()->to('student/request-control-number')->with('message','Bill for other fees created successfully');
      }else{
        return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
      }

    }

    /**
     * Show bank information
     */
    public function showBankInfo(Request $request)
    {
        $student = User::find(Auth::user()->id)->student()->with('applicant:id,campus_id,intake_id')->first();
        
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }
  
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

        $loan_allocation = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                        ->where('campus_id',$student->applicant->campus_id)
                                        ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                        ->first();
        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();
        $data = [
          'study_academic_year'=>$study_academic_year,
          'student'=>$student,
          'loan_allocation'=>$loan_allocation,
          'loan_status'=>$loan_status
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
        $student = User::find(Auth::user()->id)->student()->with('applicant:id,campus_id,intake_id')->first();

        $study_academic_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
          $ac_yr_id = $study_academic_year->id + 1;
        }else{
          $ac_yr_id = $study_academic_year->id;
        }
  
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();                  

        
        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();
        $data = [
		   'study_academic_year'=>$study_academic_year,
           'student'=>$student,
           'loan_allocations'=>LoanAllocation::with(['studyAcademicYear.academicYear'])->where('registration_number',$student->registration_number)->where('campus_id',$student->applicant->campus_id)->latest()->paginate(20),
           'loan_status'=>$loan_status
        ];
        return view('dashboard.student.loan-allocations',$data)->withTitle('Loan Allocations');
    }

    /**
     * Show postponements
     */
    public function requestPostponement(Request $request)
    {
        $student = User::find(Auth::user()->id)->student()->with('applicant:id,campus_id,intake_id')->first();

        $study_academic_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
        $semester = Semester::find(session('active_semester_id'));

        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){

          if($semester->id == 2){
            $semester_id = 1;
          }else{
            $semester_id = $semester->id;
          }
          $ac_yr_id = $study_academic_year->id + 1;
        }else{

          $semester_id = $semester->id;
          $ac_yr_id = $study_academic_year->id;
        }
  
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();  

        $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();        
        $data = [
           'study_academic_year'=>$study_academic_year,
           'semester'=>Semester::where('id',$semester_id)->first(),
           'student'=>$student,
           'postponements'=>Postponement::where('student_id',$student->id)->latest()->paginate(20),
           'loan_status'=>$loan_status
        ];
        return view('dashboard.student.postponements',$data)->withTitle('Postponements');

    }

	/**
	 * Show indicate continue
	 */
	 public function showIndicateContinue(Request $request)
	 {
		 $student = User::find(Auth::user()->id)->student()->with('applicant:id,intake_id,campus_id,program_level_id,index_number')->first();

	//	 if($student->continue_status == 1){
			$applicant = Applicant::where('index_number',$student->applicant->index_number)->where('is_continue', 1)->latest()->first();

/* 		 $applicant = Applicant::where('index_number',$student->applicant->index_number)->with(['selections.campusProgram.program','selections'=>function($query){
                $query->orderBy('order','asc');
            },'nectaResultDetails'=>function($query){
                 $query->where('verified',1);
            },'nacteResultDetails'=>function($query){
                 $query->where('verified',1);
            },'outResultDetails'=>function($query){
                 $query->where('verified',1);
            },'selections.campusProgram.campus','nectaResultDetails.results','nacteResultDetails.results','outResultDetails.results','programLevel','applicationWindow'])->where('campus_id',$student->applicant->campus_id)->latest()->first();
 */
/*         $window = $applicant->applicationWindow;

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
		} */

    $study_academic_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();

    if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
      $ac_yr_id = $study_academic_year->id + 1;
    }else{
      $ac_yr_id = $study_academic_year->id;
    }

    $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();
		if($applicant){

		$data = [
		   'study_academic_year'=>$ac_year,
//           'applicant'=>$applicant,
		   'selected_campus'=>$applicant->campus_id,
		   'programme'=>Award::where('id', $applicant->program_level_id)->first(),
           //'campus'=>Campus::find($student->campus_id),
           //'application_window'=>$window,
           //'campus_programs'=>$window? $programs : [],
           'campuses'=>Campus::all(),
		   'student'=>$student,
          // 'program_fee_invoice'=>Invoice::where('payable_id',$student->id)->where('payable_type','student')->first(),
          // 'request'=>$request
        ];

		}else{
      if(Graduant::where('student_id',$student->id)->where('status','GRADUATING')->count() == 0){
        return redirect()->back()->with('error','You are not in the graduants list, please check with the Examination Office');
      }

      if(Clearance::where('student_id',$student->id)->where('library_status',1)->where('hostel_status',1)->where('finance_status',1)->where('hod_status',1)->count() == 0){
        return redirect()->back()->with('error','You have not finished clearance');
      }

      $loan_status = LoanAllocation::where(function($query) use($student){$query->where('student_id',$student->id)->orWhere('applicant_id',$student->applicant_id);})
                                    ->where('campus_id',$student->applicant->campus_id)
                                    ->where('study_academic_year_id',$study_academic_year->academicYear->id)
                                    ->count();

		$data = [
		   'study_academic_year'=>$ac_year,
		   'selected_campus'=>[],
		   'programme'=>[],
           'campuses'=>Campus::all(),
		   'student'=>$student,
       'loan_status'=>$loan_status
        ];
		}

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
        DB::rollback();
			  return redirect()->back()->with('error','You have already indicated your intention to continue');
		  }
		  if(!$student){
        DB::rollback();
			  return redirect()->back()->with('error','You cannot indicate to continue with upper level');
		  }
/* 		  $application_window = ApplicationWindow::where('campus_id',$student->applicant->campus_id)->where('status','ACTIVE')->latest()->first();
		  if(!$application_window){
			  return redirect()->back()->with('error','Application window not defined');
		  } */



		  $past_level = $student->applicant->programLevel;
		  if(str_contains($past_level->code,'HD')){
        DB::rollback();
			  return redirect()->back()->with('error','You do not have to indicate, proceed with registration.');
		  }elseif(str_contains($past_level->code,'BTC')){
			  $level = Award::where('code','OD')->first();
		  }elseif(str_contains($past_level->code,'OD')){
			  $level = Award::where('code','HD')->first();
		 /*  }elseif(str_contains($past_level->code,'HD')){
			  $level = Award::where('code','BD')->first(); */
		  }elseif(str_contains($past_level->code,'BD')){
			  $level = Award::where('code','MD')->first();
		  }

/*           $window = $application_window;

		  $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($level){
		   $query->where('award_id',$level->id);
		  })->with(['program','campus','entryRequirements'=>function($query) use($window){
			$query->where('application_window_id',$window->id);
		  }])->where('campus_id',$request->get('campus_id'))->get() : [];

		  if(count($campus_programs) == 0){
			return redirect()->back()->with('error','Selected campus does not have programmes');
		  } */

		  $applicant = new Applicant;
		  $applicant->first_name = $student->applicant->first_name;
		  $applicant->middle_name = $student->applicant->middle_name;
		  $applicant->surname = $student->applicant->surname;
		  $applicant->index_number = $student->applicant->index_number;
		  $applicant->entry_mode = 'EQUIVALENT';
		  $applicant->campus_id = $request->get('campus_id');
//		  $applicant->intake_id = $student->applicant->intake_id;
//		  $applicant->application_window_id = $application_window->id;
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
		  $applicant->veta_certificate = $student->applicant->veta_certificate;
		  $applicant->veta_status = $student->applicant->veta_status;
		  $applicant->avn_no_results = $student->applicant->avn_no_results;
		  $applicant->teacher_certificate_status = $student->applicant->teacher_certificate_status;
		  $applicant->basic_info_complete_status = 1;
		  $applicant->documents_complete_status = 1;
		  $applicant->next_of_kin_complete_status = 1;
		  $applicant->results_complete_status = 1;
		  $applicant->program_level_id = $level->id;
		  $applicant->is_continue = 1;
		  $applicant->created_at = now();



		  $user = new User;
		  $user->username = $applicant->index_number;
		  $user->email = $applicant->email;
		  $user->password = Hash::make($student->applicant->index_number);
      $user->must_update_password = 1;
		  $user->save();

		  //$old_user = User::find($student->user_id);
		  //$old_user->status = 'INACTIVE';
		  //$old_user->save();

		  $role = Role::where('name','applicant')->first();
		  $user->roles()->attach([$role->id]);

		  $applicant->user_id = $user->id;
		  $applicant->nacte_reg_no = $student->registration_number;
		  $applicant->save();

		  NectaResultDetail::where('applicant_id',$student->applicant_id)->update(['applicant_id'=>$applicant->id]);
		  NectaResult::where('applicant_id',$student->applicant_id)->update(['applicant_id'=>$applicant->id]);

      $results = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($student){$query->where('year_of_study',$student->year_of_study);})
                                  ->where('student_id',$student->id)
                                  ->get();

			$graduation_date = SpecialDate::select(DB::raw('YEAR(date) year'))->where('name', 'Graduation')->where('campus_id', $student->applicant->campus_id)->latest()->first();

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
			//$detail->diploma_category = 'Category';
			$detail->diploma_graduation_year = $graduation_date->year;
			$detail->username = $student->surname;
			$detail->date_birth = $student->birth_date;
			$detail->applicant_id = $applicant->id;
			$detail->verified = 1;
			$detail->created_at = now();
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
		  return redirect()->to('student/show-indicate-continue')->with('message','You have indicated to continue with upper level successfully');
	  }

    /**
     * Update details
     */
    public function updateDetails(Request $request)
    {
        $student = Student::find($request->get('student_id'));
        $student->studentship_status_id = $request->get('studentship_status_id');
        $student->save();

        return redirect()->back()->with('message','Student details updated successfully');
    }

	/**
     * Update specified staff details
     */
    public function editDetails(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'address'=>'required',
            'phone'=>'required',
            'email'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new StudentAction)->update($request);

        return Util::requestResponse($request,'Your profile updated successfully');
    }

    /**
     * Display modules
     */
    public function searchForStudent(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;
      $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
      $activeSemester = Semester::where('status','ACTIVE')->first();
      $applicant = Applicant::select('id')->where('index_number',$request->keyword)->where('campus_id',$staff->campus_id)->latest()->first();
      $applicant_id = $applicant? $applicant->id : 0;
      $student = Student::with(['applicant.country','applicant.district','applicant.ward','campusProgram.campus','disabilityStatus','applicant','campusProgram.program','studentShipStatus','applicant.nextOfKin',
                                'applicant.nextOfKin.country','applicant.nextOfKin.district','applicant.nextOfKin.ward','applicant.intake:id,name'])
                        ->where(function($query) use($request,$applicant_id){$query->where('registration_number', $request->keyword)
                        ->orWhere('surname',$request->keyword)->orWhere('applicant_id',$applicant_id);})->first();

      $student_id = $student? $student->id : 0;

      if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){

        if($activeSemester->id == 2){
          $semester_id = 1;
        }else{
          $semester_id = $activeSemester->id;
        }
        $ac_yr_id = $ac_year->id + 1;
      }else{

        $semester_id = $activeSemester->id;
        $ac_yr_id = $ac_year->id;
      }

      $ac_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first();

      if($student){
        $total_fee_paid_amount = null;
        $student_payments = Invoice::where('payable_id', $student_id)->where('payable_type','student')
                                   ->orWhere(function($query) use($student){$query->where('payable_id',$student->applicant->id)
                                   ->where('payable_type','applicant');})->with('feeType','gatewayPayment')->whereNotNull('gateway_payment_id')->get();

        $tuition_fee_loan = LoanAllocation::where('student_id',$student->id)->where('year_of_study',$student->year_of_study)->where('study_academic_year_id',$ac_year->academicYear->id)
        ->where('campus_id',$student->applicant->campus_id)->sum('tuition_fee');

        if(count($student_payments) > 0){
          foreach($student_payments as $payment){
            if(str_contains($payment->feeType->name, 'Tuition')){
                $total_fee_paid_amount = GatewayPayment::where('bill_id', $payment->reference_no)->sum('paid_amount');
                break;
            }
          }
        }

        $invoice = Invoice::whereNull('gateway_payment_id')->where(function($query) use($student, $student_id){$query->where('payable_id',$student->applicant->id)->where('payable_type','applicant')
                          ->orWhere('payable_id',$student_id)->where('payable_type','student');})->first();
      }
      $id_print_status = 0;
      if($student){
        $id_print_status = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester_id)->where('id_print_status',1)->count();

      }

      $data = [
          'student'=>$student,
          'student_payments'=> $student? $student_payments : null,
          'tuition_fee_loan'=> $student? $tuition_fee_loan : null,
          'total_paid_fee'=> $student? $total_fee_paid_amount : null,
          'id_print_status'=>$id_print_status,
          'invoice'=> $student && Auth::user()->hasRole('finance-officer')? $invoice : null
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
        $user->must_update_password = 1;
        $user->save();

        return redirect()->back()->with('message','Password reset successfully');
    }

        /**
     * Reset password
     */
    public function resetIDPrintStatus(Request $request)
    { 
      $student = Student::where('id',$request->get('student_id'))->with('applicant.intake:id,name')->first();
      $ac_year = StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first();
      $activeSemester = Semester::where('status','ACTIVE')->first();

      if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$ac_year->academicYear->year)[1],2)){

        if($activeSemester->id == 2){
          $semester_id = 1;
        }else{
          $semester_id = $activeSemester->id;
        }
        $ac_yr_id = $ac_year->id + 1;
      }else{

        $semester_id = $activeSemester->id;
        $ac_yr_id = $ac_year->id;
      }

        Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester_id)
                    ->update(['id_sn_no'=>null,'id_print_date'=>null,'id_print_status'=>0]);

        return redirect()->back()->with('message','ID card print status reset successfully');
    }
     /**
     * Reset control number
     */
    public function resetControlNumber(Request $request)
    { 
      $staff = User::find(Auth::user()->id)->staff;
      $invoice = Invoice::where('reference_no',$request->get('reference_no'))->first();
      $invoice->payable_id = 0; 
      $invoice->reset_by_staff_id = $staff->id;
      $invoice->save();

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
