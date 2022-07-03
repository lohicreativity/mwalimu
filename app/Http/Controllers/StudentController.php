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
use App\Domain\Academic\Models\Postponement;
use App\Domain\Registration\Models\Registration;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Settings\Models\Currency;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Auth, Validator;

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
            'registration'=>Registration::where('student_id',$student->id)->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->first()
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
      $student = User::find(Auth::user()->id)->student;
    	$data = [
            'student'=>$student,
            'invoices'=>Invoice::where('payable_id',$student->id)->where('payable_type','student')->with(['feeType','gatewayPayment'])->latest()->paginate(20)
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
    	 $student = User::find(Auth::user()->id)->student;
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
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
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id){
             $query->where('study_academic_year_id',$ac_yr_id);
         })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study){
             $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
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
           'invoices'=>Invoice::where('payable_id',$student->id)->where('payable_type','student')->with(['feeType','gatewayPayment'])->latest()->paginate(20)
        ];
        return view('dashboard.student.request-control-number',$data)->withTItle('Request Control Number');
    }

    /**
     * Request control number 
     */
    public function requestPaymentControlNumber(Request $request)
    {
        $student = Student::with('applicant')->find($request->get('student_id'));
        $email = $student->email? $student->email : 'admission@mnma.ac.tz';

        $study_academic_year = StudyAcademicYear::find(session('active_academic_year_id'));
        $usd_currency = Currency::where('code','USD')->first();

        if($request->get('fee_type') == 'TUITION'){
            $existing_tuition_invoice = Invoice::whereHas('feeType',function($query){
                $query->where('name','LIKE','%Tuition%');
            })->where('applicable_type','academic_year')->where('applicable_id',$study_academic_year->id)->first();

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

                 if(str_contains($student->applicant->nationality,'Tanzania')){
                     $amount_without_loan = round($program_fee->amount_in_tzs);
                 }else{
                     $amount_without_loan = round($program_fee->amount_in_usd*$usd_currency->factor);
                 }
          
            
            if($amount != 0.00){
                  $invoice = new Invoice;
                  $invoice->reference_no = 'MNMA-TF-'.time();
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
                                              $applicant->id,
                                              $applicant->first_name.' '.$applicant->surname,
                                              $applicant->phone,
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
                                            $applicant->id,
                                            $applicant->first_name.' '.$applicant->surname,
                                            $applicant->phone,
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
                $invoice->amount = $amount;
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
                                            $applicant->id,
                                            $applicant->first_name.' '.$applicant->surname,
                                            $applicant->phone,
                                            $email,
                                            $generated_by,
                                            $approved_by,
                                            $feeType->duration,
                                            $invoice->currency);

            } 
        }

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
