<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\NextOfKin;
use App\Domain\Application\Models\ApplicationCycle;
use App\Domain\Application\Actions\ApplicantAction;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\OutResult;
use App\Domain\Application\Models\OutResultDetail;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Models\HealthInsurance;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Currency;
use App\Http\Controllers\NHIFService;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Carbon\Carbon;
use App\Utils\DateMaker;
use Validator, Auth, Hash;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Registration\Models\Registration;

class ApplicantController extends Controller
{
    /**
     * Display a list of applicants
     */
    public function index()
    {
    	$data = [
           'applicants'=>Applicant::paginate(20)
    	];
    	return view('dashboard.application.applicants',$data)->withTitle('Applicants');
    }


    /**
     * Display login form
     */
    public function showLogin(Request $request)
    {
        $data = [
           'campuses'=>Campus::all()
        ];
        return view('auth.applicant-login',$data)->withTitle('Applicant Login');
    }

    /**
     * Authenticate student
     */
    public function authenticate(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'index_number'=>'required',
            'password'=>'required',
            'campus_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $credentials = [
            'username'=>$request->get('index_number'),
            'password'=>$request->get('password'),
			'status'=>'ACTIVE'
        ];

        $campus = Campus::find($request->get('campus_id'));

        $applicant = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',0)->first();

        $appl = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$request->get('campus_id'))->where(function($query){
             $query->where('status','SELECTED')->orWhere('status','ADMITTED')->orWhere('status',null);
        })->first();

        $tamisemi_applicant = Applicant::where('index_number',$request->get('index_number'))->where('is_tamisemi',1)->first();
        
        $window = ApplicationWindow::where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
        ->where('campus_id',$request->get('campus_id'))
        ->where('status','ACTIVE')->latest()->first();

        $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        ->where('end_date','>=', implode('-', explode('-', now()->format('Y-m-d'))))
        //->where('intake_id', $appl->intake_id)
        ->where('status','INACTIVE')->latest()->first();

        if($closed_window && !$window){
            return redirect()->back()->with('error','Application window is not active');
        }

        if(!$tamisemi_applicant){
          if(!$window && !$appl){
            return  redirect()->back()->with('error','Application window for '.$campus->name.' is not open.');
          }
        }

        if(Auth::attempt($credentials)){

            session(['applicant_campus_id'=>$request->get('campus_id')]);
            $continue_applicant = Applicant::where('user_id',Auth::user()->id)->where('is_continue', 1)->first();
            if($continue_applicant){
               $campus = Campus::where('id', $continue_applicant->campus_id)->first();
               
            }
			
            if(!Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->first() && !$continue_applicant){
                $app = Applicant::where('user_id',Auth::user()->id)->where('campus_id',0)->first();
                if($app){
                    $applicant = $app;
                    $applicant->user_id = Auth::user()->id;
                    $applicant->index_number = $app->index_number;
                    $applicant->entry_mode = $app->entry_mode;
                    $applicant->program_level_id = $app->program_level_id;
                    $applicant->intake_id = $app->intake_id;
                    $applicant->campus_id = $request->get('campus_id');
                    $applicant->application_window_id = $window->id;
                    $applicant->intake_id = $window->intake_id;
                    $applicant->first_name = $app->first_name;
                    $applicant->middle_name = $app->middle_name;
                    $applicant->surname = $app->surname;
                    $applicant->email = $app->email;
                    $applicant->phone = $app->phone;
                    $applicant->birth_date = $app->birth_date;
                    $applicant->nationality = $app->nationality;
                    $applicant->gender = $app->gender;
                    $applicant->disability_status_id = $app->disability_status_id;
                    $applicant->address = $app->address;
                    $applicant->country_id = $app->country_id;
                    $applicant->region_id = $app->region_id;
                    $applicant->district_id = $app->district_id;
                    $applicant->ward_id = $app->ward_id;
                    $applicant->street = $app->street;
                    $applicant->nin = $app->nin;
                    $applicant->is_tcu_verified = $app->is_tcu_verified;
                    $applicant->diploma_certificate = $app->diploma_certificate;
                    $applicant->basic_info_complete_status = $app->basic_info_complete_status;
                    $applicant->results_complete_status = $app->results_complete_status;
                    $applicant->teacher_diploma_certificate = $app->teacher_diploma_certificate;
                    $applicant->veta_certificate = $app->veta_certificate;
                    $applicant->veta_status = $app->veta_status;
                    $applicant->rank_points = $app->rank_points;
                    $applicant->nacte_reg_no = $app->nacte_reg_no;
                    $applicant->avn_no_results = $app->avn_no_results;
                    $applicant->teacher_certificate_status = $app->teacher_certificate_status;
                    $applicant->next_of_kin_id = $app->next_of_kin_id;
                    $applicant->next_of_kin_complete_status = $app->next_of_kin_complete_status;
                    $applicant->birth_certificate = $app->birth_certificate;
                    $applicant->o_level_certificate = $app->o_level_certificate;
                    $applicant->a_level_certificate = $app->a_level_certificate;
                    $applicant->diploma_certificate = $app->diploma_certificate; 
                    $applicant->documents_complete_status = $app->documents_complete_status;
                    $applicant->save();
					
                  session(['applicant_campus_id'=>$request->get('campus_id')]);
                  return redirect()->to('application/dashboard')->with('message','Logged in successfully');

                }elseif($app = Applicant::where('user_id',Auth::user()->id)->where('campus_id','!=',$request->get('campus_id'))->first()){
                    if($app){
                        $applicant = new Applicant;
                        $applicant->user_id = Auth::user()->id;
                        $applicant->index_number = $app->index_number;
                        $applicant->entry_mode = $app->entry_mode;
                        $applicant->program_level_id = $app->program_level_id;
                        $applicant->intake_id = $app->intake_id;
                        $applicant->campus_id = $request->get('campus_id');
                        $applicant->application_window_id = $window->id;
                        $applicant->intake_id = $window->intake_id;
                        $applicant->first_name = $app->first_name;
                        $applicant->middle_name = $app->middle_name;
                        $applicant->surname = $app->surname;
                        $applicant->email = $app->email;
                        $applicant->phone = $app->phone;
                        $applicant->birth_date = $app->birth_date;
                        $applicant->nationality = $app->nationality;
                        $applicant->gender = $app->gender;
                        $applicant->disability_status_id = $app->disability_status_id;
                        $applicant->address = $app->address;
                        $applicant->country_id = $app->country_id;
                        $applicant->region_id = $app->region_id;
                        $applicant->district_id = $app->district_id;
                        $applicant->ward_id = $app->ward_id;
                        $applicant->street = $app->street;
                        $applicant->nin = $app->nin;
                        $applicant->is_tcu_verified = $app->is_tcu_verified;
                        $applicant->diploma_certificate = $app->diploma_certificate;
                        $applicant->basic_info_complete_status = $app->basic_info_complete_status;
                        $applicant->results_complete_status = $app->results_complete_status;
                        $applicant->teacher_diploma_certificate = $app->teacher_diploma_certificate;
                        $applicant->veta_certificate = $app->veta_certificate;
                        $applicant->veta_status = $app->veta_status;
                        $applicant->rank_points = $app->rank_points;
                        $applicant->nacte_reg_no = $app->nacte_reg_no;
                        $applicant->avn_no_results = $app->avn_no_results;
                        $applicant->teacher_certificate_status = $app->teacher_certificate_status;
                        $applicant->next_of_kin_id = $app->next_of_kin_id;
                        $applicant->next_of_kin_complete_status = $app->next_of_kin_complete_status;
                        $applicant->birth_certificate = $app->birth_certificate;
                        $applicant->o_level_certificate = $app->o_level_certificate;
                        $applicant->a_level_certificate = $app->a_level_certificate;
                        $applicant->diploma_certificate = $app->diploma_certificate; 
                        $applicant->documents_complete_status = $app->documents_complete_status;
                        $applicant->save();

                        $applicants = Applicant::where('user_id',Auth::user()->id)->get();

                        foreach($applicants as $appl){
                           $necta_result_details = NectaResultDetail::where('applicant_id', $appl->id)->where('verified',1)->get();
                           $necta_change_status = $nacte_change_status = $out_change_status = false;

                           if(count($necta_result_details)>0){
                              foreach($necta_result_details as $necta_result_detail){
                                 $result_details = new NectaResultDetail;
                                 $result_details->applicant_id = $applicant->id;
                                 $result_details->center_name = $necta_result_detail->center_name;
                                 $result_details->center_number = $necta_result_detail->center_number;
                                 $result_details->first_name = $necta_result_detail->first_name;
                                 $result_details->middle_name = $necta_result_detail->middle_name;
                                 $result_details->last_name = $necta_result_detail->last_name;
                                 $result_details->index_number = $necta_result_detail->index_number;
                                 $result_details->sex = $necta_result_detail->sex;
                                 $result_details->division = $necta_result_detail->division;
                                 $result_details->points = $necta_result_detail->points;
                                 $result_details->exam_id = $necta_result_detail->exam_id;
                                 $result_details->verified = $necta_result_detail->verified;
                                 $result_details->created_at = now();
                                 $result_details->updated_at = now();
                                 $result_details->save();

                                 $result_subjects = NectaResult::where('necta_result_detail_id',$necta_result_detail->id)->get();
                                 foreach($result_subjects as $subject){
                                    $result = new NectaResult;
                                    $result->applicant_id = $applicant->id;
                                    $result->subject_code = $subject->subject_code;
                                    $result->subject_name = $subject->subject_name;
                                    $result->grade = $subject->grade;
                                    $result->necta_result_detail_id = $result_details->id;
                                    $result->created_at = now();
                                    $result->updated_at = now();
                                    $result->save();
   
                                 }
                              }
                              $necta_change_status = true;
                           } 
                           if($applicant->entry_mode == 'EQUIVALENT'){
                              $nacte_result_details = NacteResultDetail::where('applicant_id', $appl->id)->where('verified',1)->get();
                              $out_result_details = OutResultDetail::where('applicant_id', $appl->id)->where('verified',1)->get();
                              if($nacte_result_details){
                                 foreach($nacte_result_details as $nacte_result_detail){
                                    $result_details = new NacteResultDetail;
                                    $result_details->applicant_id = $applicant->id;
                                    $result_details->institution = $nacte_result_detail->institution;
                                    $result_details->programme = $nacte_result_detail->programme;
                                    $result_details->firstname = $nacte_result_detail->firstname;
                                    $result_details->middlename = $nacte_result_detail->middlename;
                                    $result_details->surname = $nacte_result_detail->surname;
                                    $result_details->avn = $nacte_result_detail->avn;
                                    $result_details->gender = $nacte_result_detail->gender;
                                    $result_details->diploma_gpa = $nacte_result_detail->diploma_gpa;
                                    $result_details->diploma_code = $nacte_result_detail->diploma_code;
                                    $result_details->diploma_category = $nacte_result_detail->diploma_category;
                                    $result_details->diploma_graduation_year = $nacte_result_detail->diploma_graduation_year;
                                    $result_details->username = $nacte_result_detail->username;
                                    $result_details->registration_number = $nacte_result_detail->registration_number;
                                    $result_details->date_birth = $nacte_result_detail->date_birth;
                                    $result_details->verified = $nacte_result_detail->verified;
                                    $result_details->created_at = now();
                                    $result_details->updated_at = now();
                                    $result_details->save();
      
                                    $result_subjects = NacteResult::where('nacte_result_detail_id',$nacte_result_detail->id)->get();
                                    foreach($result_subjects as $subject){
                                       $result = new NacteResult;
                                       $result->applicant_id = $applicant->id;
                                       $result->subject = $subject->subject;
                                       $result->grade =  $subject->grade;
                                       $result->nacte_result_detail_id =  $result_details->id;
                                       $result->created_at = now();
                                       $result->updated_at = now();
                                       $result->save();
      
                                    }
                                 }
                                 $nacte_change_status = true;
                              }
   
                              if($out_result_details){
                                 foreach($out_result_details as $out_result_detail){
                                    $result_details = new OutResultDetail;
                                    $result_details->applicant_id = $applicant->id;
                                    $result_details->reg_no = $out_result_detail->reg_no;
                                    $result_details->index_number = $out_result_detail->index_number;
                                    $result_details->first_name = $out_result_detail->first_name;
                                    $result_details->middle_name = $out_result_detail->middle_name;
                                    $result_details->surname = $out_result_detail->surname;
                                    $result_details->gender = $out_result_detail->gender;
                                    $result_details->gpa = $out_result_detail->gpa;
                                    $result_details->classification = $out_result_detail->classification;
                                    $result_details->birth_date = $out_result_detail->birth_date;
                                    $result_details->academic_year = $out_result_detail->academic_year;
                                    $result_details->verified = $out_result_detail->verified;
                                    $result_details->created_at = now();
                                    $result_details->updated_at = now();
                                    $result_details->save();
      
                                    $result_subjects = OutResult::where('out_result_detail_id',$out_result_detail->id)->get();
                                    foreach($result_subjects as $subject){
                                       $result = new OutResult;
                                       $result->subject_name = $subject->subject_name;
                                       $result->subject_code = $subject->subject_code;
                                       $result->grade =  $subject->grade;
                                       $result->out_result_detail_id =  $result_details->id;
                                       $result->created_at = now();
                                       $result->updated_at = now();
                                       $result->save();
      
                                    }
      
                                 }
                                 $out_change_status = true;
                              }
                           }
                           if($applicant->entry_mode == 'DIRECT' && $necta_change_status){
                              break;
                           }elseif($applicant->entry_mode == 'EQUIVALENT' && $necta_change_status && ($nacte_change_status || out_change_status)){
                              break;
                           }                     
                       }
                    }
				    }
                session(['applicant_campus_id'=>$request->get('campus_id')]);
                return redirect()->to('application/dashboard')->with('message','Logged in successfully');
			
                }elseif(!Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->first() && $continue_applicant){
                  return redirect()->back()->with('error','Incorrect campus. Please log in to '.$campus->name);
            
               }elseif(Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->where('submission_complete_status', 0)->first() && $continue_applicant){
                  if($continue_applicant->application_window_id == null){
                     $app = Applicant::where('user_id',Auth::user()->id)->where('is_continue', 1)->where('application_window_id', null)->first();
                     $continue_applicant = $app;
                     $continue_applicant->application_window_id = $window->id;
                     $continue_applicant->intake_id = $window->intake_id;
                     $continue_applicant->save();
                  }
                  session(['applicant_campus_id'=>$request->get('campus_id')]);
                  return redirect()->to('application/dashboard')->with('message','Logged in successfully');
               }else{
		  
		  
		  /* else{
			if($continue_applicant->application_window = null){
				
			} */
		  
            
            
                  session(['applicant_campus_id'=>$request->get('campus_id')]);
		            return redirect()->to('application/dashboard')->with('message','Logged in successfully');
             }
        }else{
           return redirect()->back()->with('error','Incorrect index number or password');
        }
    }

    /**
     * Applicant dashboard
     */
    public function dashboard(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
        if($applicant->basic_info_complete_status == 1 && $applicant->submission_complete_status == 0 && $applicant->status == null){
          if($applicant->next_of_kin_complete_status == 1){
              if($applicant->payment_complete_status == 1){
                  if($applicant->results_complete_status == 1){
                     if($applicant->programs_complete_status == 1){
                         return redirect()->to('application/submission');
                     }else{
                         return redirect()->to('application/select-programs');
                     }
                  }else{
                     return redirect()->to('application/results');
                  }
              }else{
                 return redirect()->to('application/payments');
              }
          }else{
              return redirect()->to('application/next-of-kin');
          }
        }else{
            return redirect()->to('application/basic-information');
        }
        $data = [
           'applicant'=>$applicant
        ];
        return view('dashboard.application.dashboard',$data)->withTitle('Dashboard');
    }

    /**
     * Edit basic information
     */
    public function editBasicInfo(Request $request)
    {
        $applicant = User::find(Auth::user()->id)
        ->applicants()
        ->with(['programLevel'])
        ->where('campus_id',session('applicant_campus_id'))->first();

        if($applicant->status=='ADMITTED'){
            $application_window = ApplicationWindow::where('id',$applicant->application_window_id)->first();
        }else{
            $application_window = ApplicationWindow::where('campus_id',session('applicant_campus_id'))
            ->where('intake_id', $applicant->intake_id)
            ->where('begin_date','<=',now()->format('Y-m-d'))
            ->where('end_date','>=',now()->format('Y-m-d'))
            ->where('status','ACTIVE')->first();

            if($applicant->is_tamisemi !== 1 && $applicant->is_transfered != 1){
               if(!$application_window){ 
                  if($applicant->status == null){
                     return redirect()->to('application/submission')->with('error','Application window already closed');
                  }
                  if($applicant->multiple_admissions !== null && $applicant->status == 'SELECTED'){
                     return redirect()->to('application/admission-confirmation')->with('error','Application window already closed');
                  }
               }
           }                 
        }
				
        if($applicant->is_tcu_verified === null && str_contains($applicant->programLevel->name,'Degree')){
            $url='http://api.tcu.go.tz/applicants/checkStatus';
            $fullindex=str_replace('-','/',Auth::user()->username);
            $xml_request='<?xml version="1.0" encoding="UTF-8"?> 
                  <Request>
                    <UsernameToken> 
                       <Username>'.config('constants.TCU_USERNAME').'</Username>
                      <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                    </UsernameToken>
                    <RequestParameters>
                      <f4indexno>'.$fullindex.'</f4indexno>
                    </RequestParameters>
                  </Request>';
              $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
              $json = json_encode($xml_response);
              $array = json_decode($json,TRUE);
             
            if(isset($array['Response'])){
              $applicant->is_tcu_verified = $array['Response']['ResponseParameters']['StatusCode'] == 202? 1 : 0;
              $applicant->save();
            }
        }

        $regulator_status = Applicant::where('program_level_id', $applicant->program_level_id)
								->whereHas('selections', function ($query) {$query->where('status', 'SELECTED')
								->orWhere('status', 'PENDING');})
								->where('application_window_id', $applicant->application_window_id)
								->where('intake_id', $applicant->intake_id)
								->count();
				
		$selected_applicants = Applicant::where('program_level_id', $applicant->program_level_id)
						->whereHas('selections',function($query) use($applicant){$query->where('application_window_id',$applicant->application_window_id);})
						->where('application_window_id', $applicant->application_window_id)
						->where('intake_id', $applicant->intake_id)->where('status', 'SELECTED')->first();
	
//        $selection_status = false;
		$selection_status = $selected_applicants != null ? true : false;
		$regulator_selection = $regulator_status != 0 ? true : false;
		
/*         if(ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('intake_id', $applicant->intake_id)
			->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
           $selection_status = $selected_applicants != null ? true : false;
         } */



         $check_selected_applicant = User::find(Auth::user()->id)->applicants()
        ->whereHas('selections', function ($query) {$query->where('status', 'SELECTED')->orWhere('status', 'PENDING');})
        ->with(['programLevel', 'selections.campusProgram.program', 'selections' => function($query) {$query->where('status', 'SELECTED')->orWhere('status', 'PENDING')->first();}])
		->where('campus_id',session('applicant_campus_id'))->first();
		
		ApplicantProgramSelection::where('application_window_id', $applicant->application_window_id)
         ->where(function($query) {
            $query->where('status', 'SELECTED')
                  ->orWhere('status', 'PENDING');
        })->with(['applicant' => function ($query) use($applicant){ $query->where('program_level_id', $applicant->program_level_id); }])->first();


        $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use ($applicant, $application_window){
               $query->where('year','LIKE','%'.date('Y',strtotime($application_window->begin_date)).'/%');
        })->first();
				
		$student = Student::where('applicant_id', $applicant->id)->first();
		$loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$study_academic_year->id)->first();
		$payment_status = false;
		$invoices = null;
		if($student){
			$invoices = Invoice::with('feeType')->where('payable_type','student')->where('payable_id',$student->id)->whereNotNull('gateway_payment_id')
								->where('applicable_id',$study_academic_year->id)->get();			
		
			if($invoices){
				$fee_payment_percent = $other_fee_payment_status = 0;
				foreach($invoices as $invoice){
					if(str_contains($invoice->feeType->name,'Tuition Fee')){
						$paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
						$fee_payment_percent = $paid_amount/$invoice->amount;         

						if($loan_allocation){
						   $fee_payment_percent = ($paid_amount+$loan_allocation->tuition_fee)/$invoice->amount;
						}
					}
 
					if(str_contains($invoice->feeType->name,'Miscellaneous')){
						$paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
						$other_fee_payment_status = $paid_amount >= $invoice->amount? 1 : 0;

					}			
				}
				if($fee_payment_percent >= 0.6 && $other_fee_payment_status == 1){
					$payment_status = true;
					$registration = Registration::where('student_id',$student->id)->where('status','UNREGISTERED')->where('study_academic_year_id',$study_academic_year->id)->where('semester_id', 1)->first();
					$registration->status = 'REGISTERED';
					$registration->save();
					
				}							
			}
		}

        $data = [
           'applicant'=>$applicant,
           'student' => $payment_status? $student : [],
           'selection_status' => $selection_status,
		   'regulator_selection' => $regulator_selection,
           'check_selected_applicant' => $check_selected_applicant,
           'application_window'=>ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->first(),
           'campus'=>Campus::find(session('applicant_campus_id')),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'status_code'=>isset($array['Response'])? $array['Response']['ResponseParameters']['StatusCode'] : null,
           'wards'=>Ward::all(),
           'disabilities'=>DisabilityStatus::all(),
        ];

/*         if($applicant->is_tamisemi !== 1 && $applicant->is_transfered != 1){
         if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
            //   if($applicant->status == null){
            //       return redirect()->to('application/submission')->with('error','Application window already closed2');
            //   }
              if($applicant->multiple_admissions !== null && $applicant->status == 'SELECTED'){
                  return view('dashboard.application.edit-basic-information',$data)->withTitle('Edit Basic Information');
              }
         }
     }
      */

        return view('dashboard.application.edit-basic-information',$data)->withTitle('Edit Basic Information');
    }

    /**
     * Send XML over POST
     */
    public function sendXmlOverPost($url,$xml_request)
    {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          // For xml, change the content-type.
          curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/xml"));
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
          // Send to remote and return data to caller.
          $result = curl_exec($ch);
          curl_close($ch);
          return $result;
    }

       /**
     * Edit basic information
     */
    public function editNextOfKin(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
        if($applicant->is_tamisemi != 1 && $applicant->is_transfered != 1){
            if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
                 return redirect()->to('application/submission')->with('error','Application window already closed');
            }
        }
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'next_of_kin'=>NextOfKin::find($applicant->next_of_kin_id),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'wards'=>Ward::all(),
           'disabilities'=>DisabilityStatus::all(),
        ];

        return view('dashboard.application.edit-next-of-kin',$data)->withTitle('Edit Next of Kin');
    }

    /**
     * Make application payment
     */
    public function payments(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with(['country','applicationWindow','programLevel'])->where('campus_id',session('applicant_campus_id'))->first();
        if($applicant->is_tamisemi != 1){
            if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
                 return redirect()->to('application/submission')->with('error','Application window already closed');
            }
        }
        $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use ($applicant){
               $query->where('year','LIKE','%'.date('Y',strtotime($applicant->applicationWindow->begin_date)).'/%');
        })->first();
        
        $invoice = Invoice::where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'fee_amount'=>FeeAmount::whereHas('feeItem.feeType',function($query){
                  $query->where('name','LIKE','%Application Fee%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first(),
           'hostel_fee_amount'=>FeeAmount::whereHas('feeItem.feeType',function($query){
                  $query->where('name','LIKE','%Hostel%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first(),
           'invoice'=>$invoice,
           'usd_currency'=>Currency::where('code','USD')->first(),
           'gateway_payment'=>$invoice? GatewayPayment::where('control_no',$invoice->control_no)->first() : null
        ];

        return view('dashboard.application.payments',$data)->withTitle('Payments');
    }

    /**
     * Request results
     */
    public function requestResults(Request $results)
    {
		$applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
		$selection_status = ApplicantProgramSelection::where('applicant_id',$applicant->id)->count();
		if($applicant->is_transfered != 1){
        if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             return redirect()->to('application/submission')->with('error','Application window already closed');
        }
		}
        
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'o_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','1')->where('verified',1)->get(),
           'a_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','2')->where('verified',1)->get(),
           'nacte_results'=>NacteResultDetail::with('results')->where('applicant_id',$applicant->id)->where('verified',1)->get(),
           'out_results'=>OutResultDetail::with('results')->where('applicant_id',$applicant->id)->where('verified',1)->get(),
		   'selection_status'=>$selection_status>0? $selection_status : 0
        ];
        return view('dashboard.application.request-results',$data)->withTitle('Request Results');
    }

    /**
     * Select programs
     */
    public function selectPrograms(Request $request)
    {
		$applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();

		$second_attempt_applicant = ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('batch_no','>',0)->first();
		if($second_attempt_applicant && $applicant->batch_no > 0){
			$applicant = Applicant::where('id',$applicant->id)->first();
			$applicant->submission_complete_status = 0;
			$applicant->programs_complete_status = 0;
			$applicant->batch_no = 0;
			$applicant->save();
		}
		
        if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             if($second_attempt_applicant){
				return redirect()->back()->with('error','Please wait for application window to be openned');				 
			 }
			 return redirect()->to('application/submission')->with('error','Application window already closed');
        }
        // $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('campus_id',session('applicant_campus_id'))->first();

        if($applicant->results_complete_status == 0){
            return redirect()->to('application/results')->with('error','You must first complete results section');
        }

        $window = $applicant->applicationWindow;

        $campus_programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                   $query->where('award_id',$applicant->program_level_id);
           })->with(['program','campus','entryRequirements'=>function($query) use($window){
                $query->where('application_window_id',$window->id);
           }])->where('campus_id',session('applicant_campus_id'))->get() : [];
        

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

                   // Certificate
                   if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
					   $o_level_other_pass_count = 0;
                       $o_level_must_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           $other_must_subject_ready = false;
                           foreach ($detail->results as $key => $result) {
                              
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

/*                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                         $o_level_must_pass_count += 1;
                                       }
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }
                                      
                                    }else{
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                          $o_level_must_pass_count += 1;
                                       }
                                       
                                       if(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                       if(!Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) && !Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                    $o_level_pass_count += 1;
                                 } */
								 
								 // lupi changed
								 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
									
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                    }else{
										if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
											$o_level_other_pass_count += 1;	
										}elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
											$o_level_other_pass_count += 1;											
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
						 if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                         //    if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){

                             $programs[] = $program;
                         }elseif($applicant->veta_status === 1){
                             $programs[] = $program;							 
						 }
/*                          if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                             if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                         //    if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){

                               $programs[] = $program;
                             }
                         }else{
							if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                            // if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                               $programs[] = $program;
                             }
                         } */
                         
                       }
                   }

                   // Diploma
                   if(str_contains($award->name,'Diploma')){
                       $o_level_pass_count = 0;
					   $o_level_other_pass_count = 0;
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


/*                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                         $o_level_must_pass_count += 1;
                                       }
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }
                                 
                                    }else{
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         // $o_level_pass_count += 1;
                                        $o_level_must_pass_count += 1;
                                       }
                                       
                                       if(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                       if(!Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) && !Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                         $o_level_pass_count += 1;
                                    }
                                 }else{
                                     $o_level_pass_count += 1;
                                 } */
								 
								if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
									
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                    }else{
										if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
											$o_level_other_pass_count += 1;	
										}elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
											$o_level_other_pass_count += 1;											
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
 /*                                if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                     if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }

                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_principle_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                         $a_level_principle_pass_count += 1;
                                    }
                                 }else{
                                    $a_level_principle_pass_count += 1;
                                 } */
								 // lupi changed this to properly check principle_pass_count | Tested
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
// Original
/*                                  if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(Util::arrayIsContainedInKey($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
									   return $a_level_subsidiary_pass_count;
                                 }else{
                                    $a_level_subsidiary_pass_count += 1;
                                 } */
								 // lupi changed to properly count subsidiary points | tested
								 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_subsidiary_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
										  
                                    }
                                 }else{
                                    $a_level_subsidiary_pass_count += 1;
                                 }
							  }
                           }
                         }
                         
                       }
                    /*    if(unserialize($program->entryRequirements[0]->must_subjects) != ''){		// original
                       if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){
                       // if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1) && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){		// original
                           $programs[] = $program;
                        }
                        }else{
					
                            if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count == 1)){
                             $programs[] = $program;
                           }
                            if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count > 1){
                             $programs[] = $program;
                           }
                        } */
						// lupi changed the code below to ignore checks on form IV must subjects
/* 					   if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){
                         $programs[] = $program;
                       }
					    */
					   if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && (($a_level_principle_pass_count > 0 
						&& ($a_level_subsidiary_pass_count + $a_level_principle_pass_count >= 2)) || $a_level_principle_pass_count >= 2)){
							$programs[] = $program;
						}
                       $has_btc = $has_diploma = $pass_diploma = false;
                      
                       if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level >= 4){
            
                           foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                                foreach($applicant->nacteResultDetails as $det){
                                    if(str_contains(strtolower($det->programme),strtolower($sub)) && str_contains(strtolower($det->programme),'basic')){
                                       $has_btc = true;
                                    }elseif(str_contains(strtolower($det->programme),'diploma')){
                                       $has_diploma = true;  
                                       if($det->diploma_gpa >= 2){
                                          $pass_diploma = true;
                                       }  
								            }
                                }
                           }
                       } elseif (unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level == 5) {
                        // salim added elseif part to check nta level 5 for diploma students

                       }else{       // lupi added the else part to determine btc status when equivalent majors have not been defined
                            foreach($applicant->nacteResultDetails as $det){
                                   if(str_contains(strtolower($det->programme),'basic')){
                                     $has_btc = true;
                                   }elseif(str_contains(strtolower($det->programme),'diploma')){
                                       $has_diploma = true;  
                                       if($det->diploma_gpa >= 2){
                                          $pass_diploma = true;
                                       }
								            }
                            }
                       }

                       if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_btc && !$has_diploma){
                           $programs[] = $program;
                       } elseif (($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $applicant->veta_status == 1) {
                           $programs[] = $program;
                       }elseif(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_btc && $has_diploma && $pass_diploma){
						      // retrieve campus programmes, with students, offered in the previous application window
                           $previous_programmes = CampusProgram::whereHas('students.applicant', function($query) use($applicant){$query->where('application_window_id',$applicant->application_window_id - 1);})
                                                               ->where('campus_id', $applicant->campus_id)->whereHas('program', function($query){$query->where('name','LIKE','%Ordinary%');})->get();
                           foreach($previous_programmes as $program){
                              $programs[] = $program;   
                           }
					        }
                   }
                   
                   // Bachelor
                   if(str_contains($award->name,'Bachelor')){

                       $o_level_pass_count = 0;
					   $o_level_other_pass_count = 0;
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

/*                                  if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
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
                                 } */
								 
								 								 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
									
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                    }else{
										if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
											$o_level_other_pass_count += 1;	
										}elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
											$o_level_other_pass_count += 1;											
										}
									}
                                }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                        $o_level_pass_count += 1;
										  
                                    }
                                }else{
                                    $o_level_pass_count += 1;
                                }
								 // lupi changed
								 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
									
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $other_must_subject_ready = true;
                                       }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                    }else{
										if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
											$o_level_other_pass_count += 1;	
										}elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
											$o_level_other_pass_count += 1;											
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
                              if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce the sample
                              /*if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                 }*/

                                // lupi changed to properly count subsidiary points | tested
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_subsidiary_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                          
                                    }
                                 }else{
                                    $a_level_subsidiary_pass_count += 1;
                                 }
                              }

                              if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){
                              // if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){ original

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
                              if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce sample size
                             // if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_subsidiary_pass_grade]){     original
/*                                 if(unserialize($program->entryRequirements[0]->subsidiary_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->subsidiary_subjects))){
                                         $a_level_out_subsidiary_pass_count += 1;
                                       }
                                 }
*/                               // lupi changed to properly count subsidiary points | tested
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_subsidiary_pass_count += 1;
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
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
                       if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                           $programs[] = $program;
                       }
                       }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){
                        
                           $programs[] = $program;
                        
                       } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                           $programs[] = $program;
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
                       $has_nacte_results = false;

                       foreach($applicant->nacteResultDetails as $detail){
                             if(count($detail->results) == 0){
                                $has_nacte_results = true;
                             }
                            $nacte_gpa = $detail->diploma_gpa;
                        }
                        
                        if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_nacte_results && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){
                                
                            $programs[] = $program;
                        }

                       if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){

                           foreach($applicant->nacteResultDetails as $detail){
                             foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){

                               if(str_contains(strtolower($detail->programme),strtolower($sub))){

                                   $has_major = true;
                               }
                             }
                             $nacte_gpa = $detail->diploma_gpa;
                           }
                       }else{
                          if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                              foreach($applicant->nacteResultDetails as $detail){
                                  foreach($detail->results as $result){
                                      foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                          if(str_contains(strtolower($result->subject),strtolower($sub))){
                                              $equivalent_must_subjects_count += 1;
                                          }
                                      }
                                  }
                                  $nacte_gpa = $detail->diploma_gpa;
                              }
                          }
                       }
                        if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){
                           // return $has_major.'-'.$o_level_pass_count.'-'.$nacte_gpa.'-'.$program->entryRequirements[0]->equivalent_gpa;
                            if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_major && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa){
                                
                               $programs[] = $program;
                            }
                        }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                            if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $nacte_gpa >= $program->entryRequirements[0]->equivalent_gpa)){
                                
                               $programs[] = $program;
                            }
                        }


                        $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects); //['OFC 017','OFP 018','OFP 020'];
                        $out_pass_subjects_count = 0;
                        
                        foreach($applicant->outResultDetails as $detail){
                            foreach($detail->results as $key => $result){
                                if(!Util::arrayIsContainedInKey($result->code, $exclude_out_subjects_codes)){
                                   if($out_grades[$result->grade] >= $out_grades['C']){
                                      $out_pass_subjects_count += 1;
                                   }
                                }
                            }
                            $out_gpa = $detail->gpa;
                      
                        }


                        if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 && $a_level_out_principle_pass_count >= 1){
                                $programs[] = $program;
                        }
                            
                        if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                            if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){
                                    $programs[] = $program;
                            }
                        }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                            if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major && $nacte_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){
                                    $programs[] = $program;
                            }
                        }

                        if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){
                              $programs[] = $program;
                        }
               }
            if($subject_count != 0){
			   $app = Applicant::find($applicant->id);
               $app->rank_points = $applicant->rank_points / $subject_count;
			   $app->save();
            }
            
        }
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'application_window'=>$window,
           'campus_programs'=>$window ? $programs : []
        ];
        return view('dashboard.application.select-programs',$data)->withTitle('Select Programmes');
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
       $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
       $student = Student::where('applicant_id', $applicant->id)->first();
       // if($applicant->is_tamisemi != 1){
       //   if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
       //         return redirect()->to('application/submission')->with('error','Application window already closed');
       //    }
       // }
       $data = [
          'applicant'=>$applicant,
          'campus'=>Campus::find(session('applicant_campus_id')),
       ];

       if ($student) {
         return redirect()->back()->with('error', 'Unable to view page');
       } else {
         return view('dashboard.application.upload-documents',$data)->withTitle('Upload Documents');
       }
    }

    /**
     * Upload Avn documents
     */
    public function uploadAvnDocuments(Request $request)
    {
       $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
       if($applicant->is_tamisemi != 1 && $applicant->is_transfered != 1){
         if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
               return redirect()->to('application/submission')->with('error','Application window already closed');
          }
       }
       $data = [
          'applicant'=>$applicant,
          'campus'=>Campus::find(session('applicant_campus_id')),
       ];
       return view('dashboard.application.upload-avn-documents',$data)->withTitle('Upload AVN Documents');
    }

    /**
     * Application submission
     */
    public function submission(Request $request)
      {
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();


         $applicants = Applicant::where('program_level_id', $applicant->program_level_id)->where('submission_complete_status', 1)->where('application_window_id', $applicant->application_window_id)->whereNotNull('status')->first();

        if(ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('intake_id', $applicant->intake_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
            if($applicant->programs_complete_status != 1){
                return redirect()->back()->with('error','You must first select programmes');
            }
        }
        $selection_status = true;
        if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('intake_id', $applicant->intake_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
           $selection_status = $applicants != null ? true : false;
        }

        $program_selection = ApplicantProgramSelection::where('applicant_id', $applicant->id)->where('application_window_id', $applicant->application_window_id)->where('status', 'SELECTED')->first();
      //   return $program_selection;

        $data = [
            'applicant'=>$applicant,
            'campus'=>Campus::find(session('applicant_campus_id')),
            'selected_status'=>$selection_status,
            'program_selection' => $program_selection
        ];
        return view('dashboard.application.submission',$data)->withTitle('Submission');
      }
      

    /**
     * Store applicant into database
     */
    public function updateBasicInfo(Request $request)
    {
         $validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'date'=>'required',
            'month'=>'required',
            'year'=>'required',
            'phone' => 'required|digits:10|regex:/(0)[0-9]/',
            // 'phone'=>'required|regex:/(255)[0-9]{9}/|not_regex:/[a-z]/|min:9',
            'address'=>'required|integer',
            'nationality'=>'required',
        ]);
    	

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(Carbon::now()->subYears(14)->format('Y-m-d') < Carbon::parse($request->get('date').'-'.$request->get('month').'-'.$request->get('year'))->format('Y-m-d')){
            return redirect()->back()->withInput()->with('error','Birth date must be before 14 years ago');
        }

        if(Carbon::now()->format('Y-m-d') < Carbon::parse($request->get('date').'-'.$request->get('month').'-'.$request->get('year'))->format('Y-m-d')){
            return redirect()->back()->withInput()->with('error','Birth date cannot be the date after today');
        }

        (new ApplicantAction)->update($request);
         
        $applicant = Applicant::find($request->get('applicant_id'));
        if($applicant->status == 'ADMITTED'){
           return redirect()->back()->with('message','Applicant updated successfully');
        }else{
           return redirect()->to('application/next-of-kin')->with('message','Applicant updated successfully');
        }
    }

    /**
     * Update specified applicant
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'birth_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new ApplicantAction)->update($request);

        return Util::requestResponse($request,'Applicant updated successfully');
    }

    /**
     * Delete recent invoice
     */
    public function deleteInvoice(Request $request)
    {
         $applicant = Applicant::find($request->get('applicant_id'));
         $invoice = Invoice::where('payable_id',$applicant->id)->where('payable_type','applicant')->latest()->first();
         if(GatewayPayment::where('control_no',$invoice->control_no)->count() == 0){
		   $invoice->payable_id = 0;
           $invoice->save();
         }
         return response()->json(['status','200']);
    }

    /**
     * Remove the specified applicant
     */
    public function destroy(Request $request, $id)
    {
        try{
            $applicant = Applicant::findOrFail($id);
            $applicant->delete();
            return redirect()->back()->with('message','Applicant deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Logout student
     */
    public function logout(Request $request)
    {
      Auth::logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
      return redirect()->to('application/login');
    }

    /**
     * Download applicants list
     */
    public function downloadApplicantsList(Request $request)
    {
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Applicants-List.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

      $application_window = ApplicationWindow::find($request->get('application_window_id'));

      if($request->get('department_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program.departments',function($query) use($request){
                 $query->where('id',$request->get('department_id'));
            })->with(['nextOfKin','intake'])->get();
        }elseif($request->get('duration') == 'today'){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('created_at','<=',now()->subDays(1))->get();
        }elseif($request->get('gender') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('gender',$request->get('gender'))->get();
        }elseif($request->get('nta_level_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'));
            })->with(['nextOfKin','intake'])->get();
        }elseif($request->get('campus_program_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections',function($query) use($request){
                 $query->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake'])->get();
        }else{
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->get();
        }

        if($request->get('status') == 'progress'){
           $applicants = Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }elseif($request->get('status') == 'completed'){
           $applicants = Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }elseif($request->get('status') == 'submitted'){
           $applicants = Applicant::where('documents_complete_status',1)->where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }elseif($request->get('status') == 'total'){
            $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }

        $callback = function() use ($applicants) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['Index Number','First Name','Middle Name','Surname','Gender','Phone Number']);
                  foreach ($applicants as $row) { 
                      fputcsv($file_handle, [$row->index_number,$row->first_name,$row->middle_name,$row->surname,$row->gender,$row->phone]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Update NVA Status
     */
    public function updateNVAStatus(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'nva_status'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
         $applicant = Applicant::find($request->get('applicant_id'));
         $applicant->nva_status = $request->get('nva_status');
         $applicant->save();

         return redirect()->back()->with('message','NVA status updated successfully');
    }

    /**
     * Show other information
     */
    public function showOtherInformation(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with(['insurances','programLevel'])->where('campus_id',session('applicant_campus_id'))->first();
        $student = Student::where('applicant_id', $applicant->id)->first();
	
        $program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
        })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
        $data = [
           'applicant'=>$applicant,
           'program_fee_invoice'=>$program_fee_invoice,
        ];

        if ($student) {
            return redirect()->back()->with('error', 'Unable to view page');
		}else {
            return view('dashboard.application.other-information',$data)->withTitle('Other Information');
        }
    }

    /**
     * Update Hostel Status
     */
    public function updateHostelStatus(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'hostel_status'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
         $applicant = Applicant::find($request->get('applicant_id'));
         $applicant->hostel_status = $request->get('hostel_status');
         $applicant->save();

         return redirect()->back()->with('message','Hostel status updated successfully');
    }

    /**
     * Postponement request
     */
    public function showPostponementRequest(Request $request)
    {
         $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
         $student = Student::where('applicant_id', $applicant->id)->first();

         $program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
         })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
         $data = [
           'applicant'=>$applicant,
           'program_fee_invoice'=>$program_fee_invoice
         ];

         if ($student) {
            return redirect()->back()->with('error', 'Unable to view page');
         } else {
            return view('dashboard.application.other-info-postponement',$data)->withTitle('Postponement Request');
         }
    }

    /**
     * Request postponement
     */
    public function requestPostponement(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'letter'=>'required|mimes:pdf',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }    

        if($request->hasFile('letter')){
             $destination = SystemLocation::uploadsDirectory();
             $request->file('letter')->move($destination, $request->file('letter')->getClientOriginalName());

             $applicant = Applicant::find($request->get('applicant_id'));
             $applicant->postponement_letter = $request->file('letter')->getClientOriginalName();
             $applicant->has_postponed = 1;
             $applicant->save();
        }

        return redirect()->back()->with('message','Postponement letter submitted successfully');

    }

    /**
     * Update Hostel Status
     */
    public function updateInsuranceStatus(Request $request)
    {
        if($request->get('insurance_name') != 'NHIF' && $request->get('insurance_status') != 0){
			$validation = Validator::make($request->all(),[
            'insurance_status'=>'required',
            'card_number'=>'required',
            'insurance_name'=>'required',
            'applicant_id'=>'required',
            'insurance_card'=>'required|mimes:pdf,png,jpeg,jpg',
            'expire_year'=>'required',
            'expire_month'=>'required',
            'expire_date'=>'required'			
			]);
			
		}elseif($request->get('insurance_name') == 'NHIF'){
			$validation = Validator::make($request->all(),[
            'insurance_status'=>'required',
            'card_number'=>'required',
            'insurance_name'=>'required',
            'applicant_id'=>'required'			
			]);			
		}else{
			$validation = Validator::make($request->all(),[
            'applicant_id'=>'required'			
			]);				
		}

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
         
         if($request->get('insurance_name') != 'NHIF' && $request->get('insurance_status') != 0){
           if(strtotime($request->get('expire_year').'-'.$request->get('expire_month').'-'.$request->get('expire_date')) <= strtotime(now())){
              return redirect()->back()->with('error','Expire date cannot be less than today\'s date');
           }else{
			  (new ApplicantAction)->uploadInsurance($request); 	
		   }
         }

		
        $applicant = Applicant::find($request->get('applicant_id'));
        		
		if($request->get('insurance_name') == 'NHIF'){
            $status_code = NHIFService::checkCardStatus($request->get('card_number'))->statusCode;
            if($status_code == 406){
                return redirect()->back()->with('error','Invalid card number. Please resubmit the correct card number or request new NHIF card.');
            }else{
               $insurance = new HealthInsurance;
               $insurance->insurance_name = 'NHIF';
               $insurance->membership_number = $request->get('card_number');
               $insurance->expire_date = null;
               $insurance->applicant_id = $applicant->id;
               $insurance->status = 'VERIFIED';
               $insurance->save();
            }
            $applicant->insurance_status = $status_code == 406? 0 : 1;
         }else{
            $applicant->insurance_status = $request->get('insurance_status');
         }
         $applicant->save();

        return redirect()->back()->with('message','Health insurance status updated successfully');
    }

    /**
     * Update health insurance
     */
    public function updateInsurance(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'insurance_name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

         $insurance = HealthInsurance::find($request->get('insurance_id'));
         $insurance->insurance_name = $request->get('insurance_name');
         $insurance->membership_number = $request->get('card_number');
         $insurance->expire_date = $request->get('expire_year').'-'.$request->get('expire_month').'-'.$request->get('expire_date');
         $insurance->save();

         return redirect()->back()->with('message','Health insurance status updated successfully');
    }

    /**
     * Upload camera image 
     */
    public function uploadCameraImage(Request $request)
    {
        $filename = 'pic_'.date('YmdHis') . '.jpeg';

        $student = Student::find($request->get('student_id'));
        $student->image = $filename;
        $student->save();

        $url = '';
        if( move_uploaded_file($_FILES['webcam']['tmp_name'],public_path().'/uploads/'.$filename) ){
           $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/public/uploads/' . $filename;
        }

        // Return image url
        return $url;
    }

    /**
     * Upload signature
     */
    public function uploadSignature(Request $request)
    {
        $upload_dir = public_path().'/signatures/';
        $file_name = 'sign_'.date('YmdHis').'.png';
        $output_file_name = 'sign_trans_'.date('YmdHis').'.png';
        $img = $request->get('sign_image');
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        $file = $upload_dir.$file_name;
        $success = file_put_contents($file, $data);

        $img = imagecreatefrompng($file); //or whatever loading function you need
        $white = imagecolorallocate($img, 255, 255, 255);
        imagecolortransparent($img, $white);
        imagepng($img, $upload_dir.$output_file_name);

        $student = Student::find($request->get('student_id'));
        $student->signature = $output_file_name;
        $student->save();
        return response()->json(['message','Signature uploaded successfully','status'=>'success']);
    }

    /**
     * Applicant details
     */

     public function applicantDetails(Request $request)
     {

         $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('admission-officer')) {

            $applicant = $request->get('index_number')? Applicant::with('nextOfKin')->where('index_number',$request->get('index_number'))->where(function($query) use($staff){
               $query->where('campus_id',$staff->campus_id)->orWhere('campus_id',0);
           })->first() : null;

        } else {

            $applicant = $request->get('index_number')? Applicant::with(['nextOfKin', 'payment'])->where('index_number',$request->get('index_number'))->where(function($query) use($staff){
               $query->orWhere('campus_id',0);
           })->first() : null;

        }

        $a_level = $request->get('index_number') ? NectaResultDetail::where('applicant_id', $applicant->id)->where('exam_id', 2)->where('verified', 1)->first() : null;

        $avn = $request->get('index_number') ? NacteResultDetail::where('applicant_id', $applicant->id)->where('verified', 1)->first() : null;
        
        $out = $request->get('index_number') ? OutResultDetail::where('applicant_id', $applicant->id)->where('verified', 1)->first() : null;
        
        $data = [
            'applicant'=> $applicant,
            'a_level' => $a_level,
            'avn' => $avn,
            'out' => $out,
            'awards'=>Award::all(),
        ];

        return view('dashboard.application.applicant-details', $data)->withTitle('Edit Applicant Details');



     }



    /**
     * Edit applicant details
     */
    public function editApplicantDetails(Request $request)
      {

         
         $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('admission-officer')) {

            $applicant = $request->get('index_number')? Applicant::with('nextOfKin')->where('index_number',$request->get('index_number'))->where(function($query) use($staff){
               $query->where('campus_id',$staff->campus_id)->orWhere('campus_id',0);
           })->first() : null;

        } else {

            $applicant = $request->get('index_number')? Applicant::with(['nextOfKin', 'payment'])->where('index_number',$request->get('index_number'))->where(function($query) use($staff){
               $query->orWhere('campus_id',0);
           })->first() : null;

        }

         $data = [
         'applicant'=> $applicant,
         'awards'=>Award::all(),
		 'countries'=>Country::all(),
         ];

         return view('dashboard.application.edit-applicant-details', $data)->withTitle('Edit Applicant Details');

         // return view('dashboard.application.edit-applicant-details',$data)->withTitle('Edit Applicant Details');
      }

    /**
     * Update applicant details
     */
    public function updateApplicantDetails(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'phone'=>'required|min:12|max:12',
            'email'=>'required|email'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        

        $applicant = Applicant::find($request->get('applicant_id'));
        if(!ApplicationWindow::where('campus_id',$applicant->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
               return redirect()->back()->with('error','Application window already closed');
        }
        if($applicant->submission_complete_status == 1){
            return redirect()->back()->with('error','Applicant details cannot be modified because the application is already submitted');
        }
        $mode_before = $applicant->entry_mode;
        $level_before = $applicant->program_level_id;
        $applicant->birth_date = DateMaker::toDBDate($request->get('dob'));
        $applicant->nationality = $request->get('nationality');		
        $applicant->phone = $request->get('phone');
        $applicant->email = $request->get('email');
        $applicant->entry_mode = $request->get('entry_mode');
        $applicant->program_level_id = $request->get('program_level_id');
        $applicant->save();

        if($mode_before != $applicant->entry_mode || $level_before != $applicant->program_level_id){
            ApplicantProgramSelection::where('applicant_id',$applicant->id)->delete();
            Applicant::where('id',$applicant->id)->update(['programs_complete_status'=>0]);
        }

        return redirect()->back()->with('message','Applicant details updated successfully');
    }

    /**
     * Update NACTE registration number
     */
    public function updateNacteRegNumber(Request $request, $nacte_reg_no)
    {

            if ($nacte_details = NacteResultDetail::where('registration_number',str_replace('-', '/', $nacte_reg_no))->where('applicant_id',$request->get('applicant_id'))->first()) {
               return response()->json(['nacte_details' => $nacte_details, 'exists' => 1]);
            } else {

               try{
                  $response = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/particulars/'.str_replace('-', '.', $nacte_reg_no).'-4/'.config('constants.NACTE_API_KEY'));
               }catch(\Exception $e){
                  return response()->json(['error'=>'Unexpected network error occured. Please try again']);
               }

               if(json_decode($response)->code != 200){
                  return response()->json(['error'=>'Invalid NACTE Registration number']);
               } else if (json_decode($response)->code == 200) {

                  $nacte_details = new NacteResultDetail;

                  $nacte_details->institution = json_decode($response)->params[0]->institution_name;
                  $nacte_details->firstname = json_decode($response)->params[0]->firstname;
                  $nacte_details->middlename = json_decode($response)->params[0]->middle_name;
                  $nacte_details->surname = json_decode($response)->params[0]->surname;
                  $nacte_details->registration_number = json_decode($response)->params[0]->registration_number;
                  $nacte_details->gender = json_decode($response)->params[0]->sex;
                  $nacte_details->diploma_gpa = json_decode($response)->params[0]->GPA;
                  $nacte_details->date_birth = json_decode($response)->params[0]->DOB;
                  $nacte_details->programme = json_decode($response)->params[0]->programme_name;
                  $nacte_details->diploma_graduation_year = json_decode($response)->params[0]->accademic_year;
                  $nacte_details->verified = 0;
                  $nacte_details->applicant_id = $request->get('applicant_id');
                  $nacte_details->save();

                  return response()->json(['nacte_details' => $nacte_details, 'exists' => 0]);

               }

               
            }



      //   $applicant = Applicant::find($request->get('applicant_id'));
      //   $applicant->nacte_reg_no = $request->get('nacte_reg_no');
      //   if(NectaResultDetail::where('applicant_id',$applicant->id)->where('verified',1)->count() != 0){
      //      $applicant->results_complete_status = 1;
      //   }
      //   $applicant->save();

      //   if($det = NacteResultDetail::where('registration_number',json_decode($response)->params[0]->registration_number)->where('applicant_id',$request->get('applicant_id'))->first()){
      //       $detail = $det;
      //   }else{
      //       $detail = new NacteResultDetail;
      //   }
      //   $detail->institution = json_decode($response)->params[0]->institution_name;
      //   $detail->firstname = json_decode($response)->params[0]->firstname;
      //   $detail->middlename = json_decode($response)->params[0]->middle_name;
      //   $detail->surname = json_decode($response)->params[0]->surname;
      //   $detail->registration_number = json_decode($response)->params[0]->registration_number;
      //   $detail->gender = json_decode($response)->params[0]->sex;
      //   $detail->diploma_gpa = json_decode($response)->params[0]->GPA;
      //   $detail->date_birth = json_decode($response)->params[0]->DOB;
      //   $detail->programme = json_decode($response)->params[0]->programme_name;
      //   $detail->diploma_graduation_year = json_decode($response)->params[0]->accademic_year;
      //   $detail->verified = 1;
      //   $detail->applicant_id = $request->get('applicant_id');
      //   $detail->save();

      //   return redirect()->back()->with('message','NACTE registration number updated successfully');
    }
	
	/**
	 * Check for receipt
	 */
	 public function checkReceipt(Request $request)
	 {
		 $invoice = Invoice::find($request->get('invoice_id'));
		 if(GatewayPayment::where('control_no',$invoice->control_no)->count() != 0){
			 return response()->json(['code'=>200]);
		 }else{
			 return response()->json(['code'=>201]);
		 }
	 }

}
