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
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Models\HealthInsurance;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\Semester;
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
use App\Domain\Application\Models\ApplicationBatch;
use App\Domain\Application\Models\ApplicantSubmissionLog;
use App\Domain\Application\Models\ApplicantFeedBackCorrection;
use App\Domain\Application\Models\ExternalTransfer;
use App\Domain\Finance\Models\ProgramFee;

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
/* return ApplicationWindow::where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first(); */
        $campus = Campus::find($request->get('campus_id'));

        $applicant = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',0)->first();

        $appl = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',$request->get('campus_id'))->where(function($query){
             $query->where('status','SELECTED')->orWhereIn('status',['ADMITTED','SUBMITTED','NOT SELECTED'])->orWhereNull('status');
        })->latest()->first();

        if(!$appl && !$applicant){
            return redirect()->back()->with('error', 'Sorry, we do not have any record matches your index number. Please check with Admission Officer.');
        }

        $tamisemi_applicant = Applicant::where('index_number',$request->get('index_number'))->where('is_tamisemi',1)->first();

        $window_batch = null;

        if($applicant){
            if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
               // $window = ApplicationWindow::where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
               // $window = ApplicationWindow::whereHas('applicationBatches', function($query) use($applicant){ $query->where('program_level_id', $applicant->program_level_id)->latest();})
               //             ->where('campus_id', $request->get('campus_id'))
               //             ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               //             ->where('status', 'ACTIVE')
               //             ->latest()->first();
            }elseif($applicant->program_level_id == 4){
               // $window = ApplicationWindow::where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            }elseif($applicant->program_level_id == 5){
               // $window = ApplicationWindow::where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            }

        }

        if($appl){
            if($appl->program_level_id == 1 || $appl->program_level_id == 2){
               // $window = ApplicationWindow::where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $appl->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            }elseif($appl->program_level_id == 4){
               // $window = ApplicationWindow::where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $appl->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            }elseif($appl->program_level_id == 5){
               // $window = ApplicationWindow::where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
               // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $appl->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            }
         }

         if(Auth::attempt($credentials)){

            $new_to_campus_applicant = Applicant::where('user_id',Auth::user()->id)->where('campus_id','!=',$request->get('campus_id'))->first();

            if($new_to_campus_applicant){
               if($new_to_campus_applicant->program_level_id == 1 || $new_to_campus_applicant->program_level_id == 2){
                  // $window = ApplicationWindow::where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $new_to_campus_applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
                  // $window = ApplicationWindow::whereHas('applicationBatches', function($query) use($new_to_campus_applicant){ $query->where('program_level_id', $new_to_campus_applicant->program_level_id)->latest();})
                  //             ->where('campus_id', $request->get('campus_id'))
                  //             ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  //             ->where('status', 'ACTIVE')
                  //             ->latest()->first();
               }elseif($new_to_campus_applicant->program_level_id == 4){
                  // $window = ApplicationWindow::where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $new_to_campus_applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
                  // $window = ApplicationWindow::whereHas('applicationBatches', function($query) use($new_to_campus_applicant){ $query->where('program_level_id', $new_to_campus_applicant->program_level_id)->latest();})
                  //             ->where('campus_id', $request->get('campus_id'))
                  //             ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  //             ->where('status', 'ACTIVE')
                  //             ->latest()->first();
               }elseif($new_to_campus_applicant->program_level_id == 5){
                  // $window = ApplicationWindow::where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  // ->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->latest()->first();
               $app_window = ApplicationWindow::where('campus_id', $request->campus_id)->where('status', 'ACTIVE')->first();
               if(!$app_window){
                  return redirect()->back()->with('error','Application window is inactive');
               }
               $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
                  $new_to_campus_applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
                  // $window = ApplicationWindow::whereHas('applicationBatches', function($query) use($new_to_campus_applicant){ $query->where('program_level_id', $new_to_campus_applicant->program_level_id)->latest();})
                  //             ->where('campus_id', $request->get('campus_id'))
                  //             ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                  //             ->where('status', 'ACTIVE')
                  //             ->latest()->first();
               }
            }
        }

// Need to crosscheck this
        // $closed_window = ApplicationWindow::where('campus_id',$request->get('campus_id'))
        // ->where('end_date','>=', implode('-', explode('-', now()->format('Y-m-d'))))
        // //->where('intake_id', $appl->intake_id)
        // ->where('status','INACTIVE')->latest()->first();

        if(!$app_window && !$window_batch){
            return redirect()->back()->with('error','Application window already closed');
        }

        if(!$tamisemi_applicant){

          if(!$window_batch && !$appl){
            return  redirect()->back()->with('error','Application window for '.$campus->name.' is already closed.');
          }
        }

        if(Auth::attempt($credentials)){
            session(['applicant_campus_id'=>$request->get('campus_id')]);
            if($tamisemi_applicant && $tamisemi_applicant->surname == null){

               if(!NectaResultDetail::where('applicant_id',$tamisemi_applicant->id)->where('exam_id',1)->where('verified',1)->first()){

                  $parts=explode("/",$tamisemi_applicant->index_number);
                  //create format from returned form four index format

                  if(str_contains($tamisemi_applicant->index_number,'EQ')){
                      $exam_year = explode('/',$tamisemi_applicant->index_number)[1];
                      $index_no = $parts[0];
                  }else{
                      $exam_year = explode('/', $tamisemi_applicant->index_number)[2];
                      $index_no = $parts[0]."-".$parts[1];
                  }

                  $response = Http::post('https://api.necta.go.tz/api/results/individual',[
                     'api_key'=>config('constants.NECTA_API_KEY'),
                     'exam_year'=>$exam_year,
                     'index_number'=>$index_no,
                     'exam_id'=>'1'
                 ]);

                 if(!isset(json_decode($response)->results)){
                     return redirect()->back()->with('error','There is a problem, please try again later.');
                 }

                  $tamisemi_applicant->first_name = json_decode($response)->particulars->first_name;
                  $tamisemi_applicant->middle_name = json_decode($response)->particulars->middle_name;
                  $tamisemi_applicant->surname = json_decode($response)->particulars->last_name;
                  $tamisemi_applicant->gender = json_decode($response)->particulars->sex;

                  $tamisemi_applicant->save();
               }
            }

            $continue_applicant = Applicant::where('user_id',Auth::user()->id)->where('is_continue', 1)->first();
            if($continue_applicant){
               $campus = Campus::where('id', $continue_applicant->campus_id)->first();

            }
            $applicant = Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->latest()->first();
            if($applicant){
               if($applicant->submission_complete_status == 1 && $applicant->status == null){
                  $applicant->documents_complete_status = 0;
                  $applicant->save();
               }
            }

            if(!Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->latest()->first() && !$continue_applicant){
               $app = Applicant::where('user_id',Auth::user()->id)->where('campus_id',0)->first();

               // New applicant
               if($app){
                  if(!$window_batch){
                     return redirect()->back()->with('error','There is no defined batch for this level. Please contact the Admission Office');
                  }
                  $applicant = $app;
                  $applicant->user_id = Auth::user()->id;
                  $applicant->index_number = $app->index_number;
                  $applicant->entry_mode = $app->entry_mode;
                  $applicant->program_level_id = $app->program_level_id;
                  $applicant->campus_id = $request->get('campus_id');
                  $applicant->application_window_id = $window_batch->application_window_id;
                  $applicant->batch_id = $window_batch->id;
                  $applicant->intake_id = $app_window->intake_id;
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

                     if(!$window_batch){
                           return redirect()->back()->with('error','There is no defined batch for this level. Please contact the Admission Office');
                     }
                     // Add an existing applicant to a new campus
                     $applicant = new Applicant;
                     $applicant->user_id = Auth::user()->id;
                     $applicant->index_number = $app->index_number;
                     $applicant->entry_mode = $app->entry_mode;
                     $applicant->program_level_id = $app->program_level_id;
                     $applicant->intake_id = $app_window->intake_id;;
                     $applicant->campus_id = $request->get('campus_id');
                     $applicant->application_window_id = $window_batch->application_window_id;
                     $applicant->batch_id = $window_batch->id;
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
                        }elseif($applicant->entry_mode == 'EQUIVALENT' && $necta_change_status && ($nacte_change_status || $out_change_status)){
                           break;
                        }
                     }
                  }
				   }

               session(['applicant_campus_id'=>$request->get('campus_id')]);
               return redirect()->to('application/dashboard')->with('message','Logged in successfully');

            }elseif(!Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->latest()->first() && $continue_applicant){
               return redirect()->back()->with('error','Incorrect campus. Please log in to '.$campus->name);

            }elseif(Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->where('submission_complete_status', 0)->latest()->first() && $continue_applicant){
               if($continue_applicant->application_window_id == null){
                  $app = Applicant::where('user_id',Auth::user()->id)->where('is_continue', 1)->where('application_window_id', null)->first();
                  $continue_applicant = $app;
                  $continue_applicant->application_window_id = $window_batch->application_window_id;
                  $continue_applicant->intake_id = $app_window->intake_id;
                  $continue_applicant->save();
               }
               session(['applicant_campus_id'=>$request->get('campus_id')]);
               return redirect()->to('application/dashboard')->with('message','Logged in successfully');
            }else{

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
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();

        if($applicant->basic_info_complete_status == 1 && $applicant->submission_complete_status == 0 && $applicant->status == null){
          if($applicant->next_of_kin_complete_status == 1){
              if($applicant->payment_complete_status == 1){
                  if($applicant->results_complete_status == 1){
                     if($applicant->programs_complete_status == 1){
                        if($applicant->documents_complete_status == 1 &&
                           ($applicant->avn_no_results === 1 || $applicant->teacher_certificate_status === 1 || $applicant->veta_status == 1 || str_contains(strtolower($applicant->programLevel->name),'masters') || (str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'EQUIVALENT'))){
                           return redirect()->to('application/submission');
                        }elseif($applicant->avn_no_results === 1 || $applicant->teacher_certificate_status === 1 || $applicant->veta_status == 1 || str_contains(strtolower($applicant->programLevel->name),'masters') || (str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'EQUIVALENT')){
                           return redirect()->to('application/upload-avn-documents');
                        }
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
      $applicant = User::find(Auth::user()->id)->applicants()->with(['programLevel'])->where('campus_id',session('applicant_campus_id'))->latest()->first();

      if($applicant->is_tcu_verified === 1 && str_contains(strtolower($applicant->programLevel->name),'bachelor') && $applicant->is_transfered == 1){
         ApplicantProgramSelection::where('applicant_id',$applicant->id)->update(['status'=>'DISCARDED']);
         ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'DISCARDED']);
      }elseif($applicant->is_tcu_verified !== 1 && str_contains(strtolower($applicant->programLevel->name),'bachelor') && $applicant->is_transfered == 1){
         ApplicantProgramSelection::where('applicant_id',$applicant->id)->update(['status'=>'ELIGIBLE']);
         ExternalTransfer::where('applicant_id',$applicant->id)->update(['status'=>'PENDING']);
      }
      $student = Student::select('id')->where('applicant_id',$applicant->id)->first();

      $regulator_status = Applicant::where('program_level_id', $applicant->program_level_id)
                                   ->whereHas('selections', function ($query) use($applicant) {$query->where('status', 'SELECTED')->orWhere('status', 'PENDING')
                                   ->where('batch_id',$applicant->batch_id);})->where('application_window_id', $applicant->application_window_id)
                                   ->where('intake_id', $applicant->intake_id)->count();

		$selected_applicants = Applicant::where('program_level_id', $applicant->program_level_id)
                                      ->whereHas('selections',function($query) use($applicant){$query->where('application_window_id',$applicant->application_window_id)
                                      ->where('status','APPROVING')->where('batch_id',$applicant->batch_id);})
                                      ->where('application_window_id', $applicant->application_window_id)
                                      ->where('intake_id', $applicant->intake_id)->first();

		$selection_status = !empty($selected_applicants)? true : false; // Check if internal selection is done
		$regulator_selection = $regulator_status != 0 ? true : false;  // Check if applicants retrieved from regulator

      //if($applicant->status != null){

      if($applicant->status=='ADMITTED' || ($applicant->status=='SELECTED') && $regulator_selection){
         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 4){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 5){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }
            // $application_window = ApplicationWindow::where('id',$applicant->application_window_id)->first();
      }else{

         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 4){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 5){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)->where('status', 'ACTIVE')->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }

         if($applicant->is_tamisemi !== 1 && $applicant->is_transfered != 1){
            if(!$window_batch){
               if($applicant->status == null || ($applicant->status == 'SELECTED' && !$regulator_selection)){
                  return redirect()->to('application/submission')->with('error','Application window already closed');
               }
               if($applicant->multiple_admissions !== null && $applicant->status == 'SELECTED'){
                  return redirect()->to('application/admission-confirmation')->with('error','Application window already closed');
               }
            }else{
               if($applicant->status != null && $applicant->status != 'SUBMITTED' && !$regulator_selection){
                  return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
               }
            }
         }
      }

      if(!$window_batch){
         if(($applicant->status == null && $applicant->is_transfered != 1) || ($applicant->status == 'SELECTED' && !$regulator_selection)){
            return redirect()->to('application/submission')->with('error','Application window already closed');
         }
         if($applicant->multiple_admissions !== null && $applicant->status == 'SELECTED'){
            return redirect()->to('application/admission-confirmation')->with('error','Application window already closed');
         }
      }else{
         if($applicant->status != null && $applicant->status != 'SUBMITTED' && !$regulator_selection){
            return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
         }
      }

      if(str_contains(strtolower($applicant->programLevel->name),'degree') && ($applicant->is_tcu_verified === null || $applicant->is_tcu_verified == 0) && $applicant->status == null){
         // && !str_contains(strtolower($applicant->programLevel->name),'master') && $applicant->is_tcu_verified == 0){

         $tcu_username = $tcu_token = null;
         if($applicant->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');

         }elseif($applicant->campus_id == 2){
               $tcu_username = config('constants.TCU_USERNAME_KARUME');
               $tcu_token = config('constants.TCU_TOKEN_KARUME');

         }

         $url='http://api.tcu.go.tz/applicants/checkStatus';
         $fullindex=str_replace('-','/',$applicant->index_number);
         $xml_request='<?xml version="1.0" encoding="UTF-8"?>
               <Request>
                  <UsernameToken>
                     <Username>'.$tcu_username.'</Username>
                     <SessionToken>'.$tcu_token.'</SessionToken>
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

/*         if(ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('intake_id', $applicant->intake_id)
			->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
           $selection_status = $selected_applicants != null ? true : false;
         } */

      $check_selected_applicant = User::find(Auth::user()->id)->applicants()
                                       ->whereHas('selections', function ($query) {$query->where('status', 'SELECTED')->orWhere('status', 'PENDING');})
                                       ->with(['programLevel', 'selections.campusProgram.program', 'selections' => function($query) {$query->whereIn('status', ['SELECTED','PENDING'])->first();}])
                                       ->where('campus_id',session('applicant_campus_id'))->latest()->first();

		/* ApplicantProgramSelection::where('application_window_id', $applicant->application_window_id)
         ->where(function($query) {
            $query->where('status', 'SELECTED')
                  ->orWhere('status', 'PENDING');
        })->with(['applicant' => function ($query) use($applicant){ $query->where('program_level_id', $applicant->program_level_id); }])->first(); */

      $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($applicant, $app_window){
            $query->where('year','LIKE','%'.date('Y',strtotime($app_window->begin_date)).'/%');})->first();

      $activeSemester = Semester::where('status', 'ACTIVE')->first();

		$student = Student::where('applicant_id', $applicant->id)->first();

      $registrationStatus = null;
      if($student){
         $registrationStatus = Registration::where('student_id', $student->id)->where('study_academic_year_id', $study_academic_year->id)->where('semester_id', $activeSemester->id)
                                           ->where('status','UNREGISTERED')->first();
      }

		$tuition_fee_loan = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$study_academic_year->id)
                                           ->where('campus_id',$applicant->campus_id)->sum('tuition_fee');

		$invoices = null;
		if($registrationStatus){
         $program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$study_academic_year->id)->first();

         if(!$program_fee){
             return redirect()->back()->with('error','Programme fee has not been defined. Please contact the Admission Office.');
         }

         if($tuition_fee_loan >= $program_fee->amount_in_tzs && LoanAllocation::where('student_id',$student->id)->where('study_academic_year_id',$study_academic_year->id)
                                                               ->where('campus_id',$applicant->campus_id)->where('has_signed',1)){
           Registration::where('student_id',$student->id)->where('study_academic_year_id',$study_academic_year->id)
           ->where('semester_id', $activeSemester->id)->update(['status'=>'REGISTERED']);

         }else{
            $invoices = Invoice::with('feeType')->where('payable_type','student')->where('payable_id',$student->id)->whereNotNull('gateway_payment_id')
            ->where('applicable_id',$study_academic_year->id)->get();

            if($invoices){
               $fee_payment_percent = 0;
               foreach($invoices as $invoice){
                  if(str_contains($invoice->feeType->name,'Tuition Fee')){
                        $paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
                        $fee_payment_percent = $paid_amount/$invoice->amount;

                        if($tuition_fee_loan>0){
                        $fee_payment_percent = ($paid_amount+$tuition_fee_loan)/$invoice->amount;
                        }
                  }

                //   if(str_contains($invoice->feeType->name,'Miscellaneous')){
                //         $paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
                //         $other_fee_payment_status = $paid_amount >= $invoice->amount? 1 : 0;

                //   }
               }

               if($fee_payment_percent >= 0.6){
                  Registration::where('student_id',$student->id)->where('study_academic_year_id',$study_academic_year->id)
                              ->where('semester_id', 1)->update(['status'=>'REGISTERED']);

               }
            }
         }
		}

      $data = [
         'applicant'=>$applicant,
         'student' => $student,
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
         'selection_released_status'=>ApplicationBatch::select('selection_released')->where('id',$applicant->batch_id)->first(),
         'registrationStatus'=>$registrationStatus ? $registrationStatus->status : null,
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


    public function sendKarumeApplicants(Request $request){

      $applicants = Applicant::where('program_level_id',4)->where('campus_id', 2)
                       ->where('is_tcu_verified',null)->get();

      //Applicant::where('program_level_id',4)->where('campus_id', 2)->where('is_tcu_verified',1)->update(['is_tcu_verified'=>null]);

      foreach($applicants as $applicant){


          $url='http://api.tcu.go.tz/applicants/checkStatus';
          $fullindex=str_replace('-','/',$applicant->index_number);
          $xml_request='<?xml version="1.0" encoding="UTF-8"?>
              <Request>
                  <UsernameToken>
                      <Username>'.config('constants.TCU_USERNAME_KARUME').'</Username>
                      <SessionToken>'.config('constants.TCU_TOKEN_KARUME').'</SessionToken>
                  </UsernameToken>
                  <RequestParameters>
                  <f4indexno>'.$fullindex.'</f4indexno>
                  </RequestParameters>
              </Request>';
          $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
          $json = json_encode($xml_response);
          $array = json_decode($json,TRUE);

          if(isset($array['Response'])){
          $applicant->is_tcu_verified = $array['Response']['ResponseParameters']['StatusCode'] == 202? 2 : 0;
          $applicant->save();
          }
      }
  }


  public function addApplicantTCU(Request $request)
  {
      ini_set('memory_limit', '-1');
      set_time_limit(120);

      $staff = User::find(Auth::user()->id)->staff;

      $tcu_username = $tcu_token = null;
      if($staff->campus_id == 1){
          $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
          $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
          $nacte_secret_key = config('constants.NACTE_API_SECRET_KIVUKONI');

      }elseif($staff->campus_id == 2){
          $tcu_username = config('constants.TCU_USERNAME_KARUME');
          $tcu_token = config('constants.TCU_TOKEN_KARUME');
          $nacte_secret_key = config('constants.NACTE_API_SECRET_KIVUKONI');

      }elseif($staff->campus_id == 3){
          $nacte_secret_key = config('constants.NACTE_API_SECRET_KIVUKONI');
      }

      $count = 0;
      $applicants = Applicant::select('id','index_number','gender','entry_mode')
                              ->where('program_level_id',4)->where('campus_id',$staff->campus_id)
                              ->whereIn('status',['SELECTED',null])->where(function($query){$query->where('is_tcu_added',null)->orWhere('is_tcu_added',0);})->where('programs_complete_status',1)
                              ->with(['nectaResultDetails:id,applicant_id,index_number,verified,exam_id','nacteResultDetails:id,applicant_id,verified,avn',
                                    'outResultDetails:id,applicant_id,verified'])->get();

      foreach($applicants as $applicant){

         //$url='https://api.tcu.go.tz/applicants/add';
         $url='http://api.tcu.go.tz/applicants/add';

         $f6indexno = null;
         foreach ($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 2 && $detail->verified == 1){
                  $f6indexno = $detail->index_number;
                  break;
            }
         }

         $otherf4indexno = [];
         foreach($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 1 && $detail->verified == 1 && $detail->index_number != $applicant->index_number){
                  $otherf4indexno[]= $detail->index_number;
            }
         }

         $otherf6indexno = [];
         foreach($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 2 && $detail->verified == 1 && $detail->index_number != $f6indexno){
                  $otherf6indexno = $detail->index_number;
            }
         }

         if(is_array($otherf4indexno)){
            $otherf4indexno=implode(', ',$otherf4indexno);
         }

         if(is_array($otherf6indexno)){
            $otherf6indexno=implode(', ',$otherf6indexno);
         }

         $category = null;
         if($applicant->entry_mode == 'DIRECT'){
            $category = 'A';

         }else{
            // Open university
            if($applicant->outResultDetails){
                  foreach($applicant->outResultDetails as $detail){
                     if($detail->verified == 1){
                        $category = 'F';
                        break;
                     }
                  }
            }

            // Diploma holders
            if($applicant->nacteResultDetails){
                  foreach($applicant->nacteResultDetails as $detail){
                     if($detail->verified == 1){
                        $f6indexno = $f6indexno == null? $detail->avn : $f6indexno;
                        $category = 'D';
                        break;
                     }
                  }
            }
         }

         $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
         <Request>
            <UsernameToken>
               <Username>'.$tcu_username.'</Username>
               <SessionToken>'.$tcu_token.'</SessionToken>
            </UsernameToken>
            <RequestParameters>
               <f4indexno>'.$applicant->index_number.'</f4indexno>
               <f6indexno>'.$f6indexno.'</f6indexno>
               <Gender>'.$applicant->gender.'</Gender>
               <Category>'.$category.'</Category>
               <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
               <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
            </RequestParameters>
         </Request>';

         $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
         $json = json_encode($xml_response);
         $array = json_decode($json,TRUE);

         if(isset($array['Response'])){
            //return $array['Response']['ResponseParameters']['StatusDescription'];
            Applicant::where('id',$applicant->id)->update(['is_tcu_added'=> $array['Response']['ResponseParameters']['StatusCode'] == 200? 1 : 0,
                                                         'is_tcu_reason'=> $array['Response']['ResponseParameters']['StatusDescription']]);
          }
/*
         if($array['Response']['ResponseParameters']['StatusCode'] == 200){
            $count++;
            Applicant::where('id',$applicant->id)->update(['tcu_added'=>1]);

         } */
      }

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
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();
        if($applicant->basic_info_complete_status == 0){
            return redirect()->to('application/basic-information');
        }
        $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
        if($applicant->is_tamisemi != 1 && $applicant->is_transfered != 1){
         //check active window
         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
         }elseif($applicant->program_level_id == 4){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 5){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }

         // if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
         //    return redirect()->to('application/submission')->with('error','Application window already closed');

         // }

            if(!$window_batch && $applicant->is_transfered != 1){
                 return redirect()->to('application/submission')->with('error','Application window already closed');
            }
            if($applicant->batch_id != $batch->id){
               return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
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
           'regulator_selection'=>false
        ];

        return view('dashboard.application.edit-next-of-kin',$data)->withTitle('Edit Next of Kin');
    }

    /**
     * Make application payment
     */
    public function payments(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with(['country','applicationWindow','programLevel'])->where('campus_id',session('applicant_campus_id'))->latest()->first();
        $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
        if($applicant->is_tamisemi != 1){
         //check if window is active
         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 4){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 5){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }

            if(!$window_batch && $applicant->is_transfered != 1){
                 return redirect()->to('application/submission')->with('error','Application window already closed');
            }
 /*            if($applicant->batch_id != $batch->id){
               return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
            } */
        }
        $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use ($applicant){
               $query->where('year','LIKE','%'.date('Y',strtotime($applicant->applicationWindow->begin_date)).'/%');
        })->first();
        $fee_amount = null;
        if(str_contains(strtolower($applicant->programLevel->name),'master')){
            $fee_amount = FeeAmount::whereHas('feeItem',function($query){$query->where('name','LIKE','%Application%')->where('name','LIKE','%Master%');})
                                ->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();
        }else{
            $fee_amount = FeeAmount::whereHas('feeItem',function($query){$query->where('name','LIKE','%Application Fee%');})
                                 ->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();
        }

        $invoice = Invoice::where('payable_id',$applicant->id)->where('payable_type','applicant')->first();

                      //check applicant program capacity
              $campus_progs = [];
              $available_progs = [];
              if($applicant->batch_id > 1 && $applicant->payment_complete_status == 0 && !str_contains(strtolower($applicant->programLevel->name),'master')){
               $window = $applicant->applicationWindow;
               $campus_programs = $window? $window->campusPrograms()
                                                   ->whereHas('program',function($query) use($applicant){$query->where('award_id',$applicant->program_level_id);})
                                                   ->with(['program','campus','entryRequirements'=>function($query) use($window){$query->where('application_window_id',$window->id);}])
                                                   ->where('campus_id', session('applicant_campus_id'))->get() : [];
               $entry_requirements = null;
               foreach($campus_programs as $prog){
                  $entry_requirements[] = EntryRequirement::select('id','campus_program_id','max_capacity')->where('application_window_id', $window->id)->where('campus_program_id',$prog->id)
                                                         ->with('campusProgram:id,code')->first();
               }

               foreach($campus_programs as $prog){

                  $count_applicants_per_program = ApplicantProgramSelection::where('campus_program_id', $prog->id)
                                                      ->where(function($query) {
                                                         $query->where('applicant_program_selections.status', 'SELECTED')
                                                               ->orWhere('applicant_program_selections.status', 'APPROVING');
                                                      })
                                                      ->count();

                                                      //return $count_applicants_per_program.'-'.$prog->entryRequirements[0]->max_capacity;
                  if ($count_applicants_per_program >= $prog->entryRequirements[0]->max_capacity) {

                     $campus_progs[] = $prog;
                  }else if($count_applicants_per_program < $prog->entryRequirements[0]->max_capacity){
                     $available_progs[] = $prog;
                  }
               }
            }

        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'fee_amount'=>$fee_amount,
           'hostel_fee_amount'=>FeeAmount::whereHas('feeItem.feeType',function($query){
                  $query->where('name','LIKE','%Hostel%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first(),
           'invoice'=>$invoice,
           'usd_currency'=>Currency::where('code','USD')->first(),
           'gateway_payment'=>$invoice? GatewayPayment::where('control_no',$invoice->control_no)->first() : null,
           'regulator_selection'=>false,
           'available_progs'=>$available_progs ?? [],
           'full_programs'=>$campus_progs ?? [],
           'all_programs'=>$campus_programs?? []
        ];
        return view('dashboard.application.payments',$data)->withTitle('Payments');
    }

    /**
     * Request results
     */
    public function requestResults(Request $results)
    {
		$applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();
      $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
      $index_number = $applicant->index_number;

      $selection_status = ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('batch_id',$applicant->batch_id)->count();
		if($applicant->is_transfered != 1){
         //check if window active
         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 4){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }elseif($applicant->program_level_id == 5){
            // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
            //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
            //                         ->where('campus_id', $applicant->campus_id)
            //                         ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
            //                         ->where('status', 'ACTIVE')
            //                         ->latest()->first();
            $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
            if(!$app_window){
               return redirect()->back()->with('error','Application window is inactive');
            }
            $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
            $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
         }

        if(!$window_batch && $applicant->is_transfered != 1){
             return redirect()->to('application/submission')->with('error','Application window already closed');
        }
        if($applicant->batch_id != $batch->id){
         return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
         }
		}

        $data = [
         'applicant'=>$applicant,
         'campus'=>Campus::find(session('applicant_campus_id')),
         'o_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','1')->where('verified',1)->get(),
         'a_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','2')->where('verified',1)->get(),
         'nacte_results'=>NacteResultDetail::with('results')->where('applicant_id',$applicant->id)->where('verified',1)->get(),
         'out_results'=>OutResultDetail::with('results')->where('applicant_id',$applicant->id)->where('verified',1)->get(),
		   'selection_status'=>$selection_status>0? 1 : 0,
         'regulator_selection'=>false
        ];
        return view('dashboard.application.request-results',$data)->withTitle('Request Results');
    }

    /**
     * Select programs
     */
    public function selectPrograms(Request $request)
    {
		$applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->latest()->first();

      $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
		$second_attempt_applicant = $request->other_attempt == true? ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('batch_id','!=',$batch->id)->first() : null;

      if(!empty($second_attempt_applicant) && $applicant->batch_id != $batch->id){
			$applicant = Applicant::where('id',$applicant->id)->first();
			$applicant->submission_complete_status = 0;
         $applicant->results_complete_status = 0;
			$applicant->programs_complete_status = 0;
			$applicant->batch_id = $batch->id;
         $applicant->status = null;
         // if(NectaResult::where('applicant_id', $applicant->id)->first()){
         //    NectaResult::where('applicant_id', $applicant->id)->delete();
         // }
         // if(NectaResultDetail::where('applicant_id', $applicant->id)->first()){
         //    NectaResultDetail::where('applicant_id', $applicant->id)->delete();
         // }
         // if(NacteResult::where('applicant_id', $applicant->id)->first()){
         //    NacteResult::where('applicant_id', $applicant->id)->delete();
         // }
         // if(NacteResultDetail::where('applicant_id', $applicant->id)->first()){
         //    NacteResultDetail::where('applicant_id', $applicant->id)->delete();
         // }
         // $out = OutResultDetail::where('applicant_id', $applicant->id)->first();
         // if($out !== null){
         //    OutResult::where('out_result_detail_id', $out->id)->delete();
         //    $out->delete();
         // }
			$applicant->save();
		}elseif($applicant->batch_id != $batch->id){
         $applicant = Applicant::where('id',$applicant->id)->first();
			$applicant->submission_complete_status = 0;
         $applicant->results_complete_status = 0;
			$applicant->programs_complete_status = 0;
			$applicant->batch_id = $batch->id;
         $applicant->status = null;
         $applicant->save();
      }
		//check if window active
      if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
         // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
         //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
         //                         ->where('campus_id', $applicant->campus_id)
         //                         ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
         //                         ->where('status', 'ACTIVE')
         //                         ->latest()->first();
         $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
         if(!$app_window){
            return redirect()->back()->with('error','Application window is inactive');
         }
         $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
         $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
      }elseif($applicant->program_level_id == 4){
         // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
         //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
         //                         ->where('campus_id', $applicant->campus_id)
         //                         ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
         //                         ->where('status', 'ACTIVE')
         //                         ->latest()->first();
         $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
         if(!$app_window){
            return redirect()->back()->with('error','Application window is inactive');
         }
         $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
         $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
      }elseif($applicant->program_level_id == 5){
         // $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
         //                         ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
         //                         ->where('campus_id', $applicant->campus_id)
         //                         ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
         //                         ->where('status', 'ACTIVE')
         //                         ->latest()->first();
         $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();
         if(!$app_window){
            return redirect()->back()->with('error','Application window is inactive');
         }
         $window_batch = ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
         $applicant->program_level_id)->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first();
      }

       if(!$window_batch){
         if($second_attempt_applicant){
            return redirect()->back()->with('error','Please wait for the application window to be openned');
         }
         return redirect()->to('application/submission')->with('error','Application window already closed');
      }
      // dd($applicant->batch_id, $batch->id);
      if($applicant->batch_id != $batch->id){
         return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
      }
      // $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('campus_id',session('applicant_campus_id'))->first();

      if($applicant->results_complete_status == 0){
         return redirect()->to('application/results')->with('error','You must complete results section first');
      }

      $o_level_selection_points = $a_level_selection_points = $diploma_selection_grade = $open_selection_grade = [];

      $campus_progs = [];
      if(!str_contains(strtolower($applicant->programLevel->name),'master')){

            $campus_progs = [];
            $available_progs = [];
            $all_programs = [];
            if($applicant->batch_id > 1 && $applicant->payment_complete_status == 1){
               $window = $applicant->applicationWindow;
               $campus_programs = $window? $window->campusPrograms()
                                                   ->whereHas('program',function($query) use($applicant){$query->where('award_id',$applicant->program_level_id);})
                                                   ->with(['program','campus','entryRequirements'=>function($query) use($window){$query->where('application_window_id',$window->id);}])
                                                   ->where('campus_id',session('applicant_campus_id'))->get() : [];
             $entry_requirements = null;
             foreach($campus_programs as $prog){
                $entry_requirements[] = EntryRequirement::select('id','campus_program_id','max_capacity')->where('application_window_id', $window->id)->where('campus_program_id',$prog->id)
                                                       ->with('campusProgram:id,code')->first();
                $all_programs[] = $prog;
             }

             foreach($campus_programs as $prog){

                $count_applicants_per_program = ApplicantProgramSelection::where('campus_program_id', $prog->id)
                                                    ->where(function($query) {
                                                       $query->where('applicant_program_selections.status', 'SELECTED')
                                                             ->orWhere('applicant_program_selections.status', 'APPROVING');
                                                    })
                                                    ->count();

                //return $count_applicants_per_program.'-'.$prog->entryRequirements[0]->max_capacity;
             if ($count_applicants_per_program >= $prog->entryRequirements[0]->max_capacity) {
                $campus_progs[] = $prog;
             }else if($count_applicants_per_program < $prog->entryRequirements[0]->max_capacity){
                $available_progs[] = $prog;
             }
          }
       }
         // dd( $campus_progs);

         $campus_programs = $available_progs;
         $award = $applicant->programLevel;
         $programs = [];

         $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

         $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

         $out_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

         $selected_program = array();

         $index_number = $applicant->index_number;
         if(str_contains($index_number,'EQ')){
         $exam_year = explode('/',$index_number)[1];
         }else{
         $exam_year = explode('/', $index_number)[2];
         }

         foreach($applicant->nectaResultDetails as $detail) {
            if($detail->exam_id == 2 && $detail->verified == 1){
               $index_number = $detail->index_number;
               if(str_contains($index_number,'EQ')){
                  $exam_year = explode('/',$index_number)[1];
               }else{
                  $exam_year = explode('/', $index_number)[2];
               }
            }
         }

         if($exam_year < 2014 || $exam_year > 2015){
            $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'E';
            $diploma_subsidiary_pass_grade = 'S';
            $principle_pass_grade = 'E';
            $subsidiary_pass_grade = 'S';
         }else{
            $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];
            $diploma_principle_pass_grade = 'D';
            $diploma_subsidiary_pass_grade = 'E';
            $principle_pass_grade = 'D';
            $subsidiary_pass_grade = 'E';
         }
           // $selected_program[$applicant->id] = false;
         $o_level_points = $a_level_points = $diploma_gpa = null;
         $subject_count = 0;
         foreach($campus_programs as $program){
            if(count($program->entryRequirements) == 0){
               return redirect()->back()->with('error',$program->program->name.' does not have entry requirements, please check with the Admission Office');
            }

            // if($program->entryRequirements[0]->max_capacity == null){
            //   return redirect()->back()->with('error',$program->program->name.' does not have maximum capacity in entry requirements');
            // }

               // Certificate
               if(str_contains($award->name,'Certificate')){
                  $o_level_pass_count = $o_level_points = 0;
                  $o_level_other_pass_count = 0;
                  $o_level_must_pass_count = 0;
                  foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                     if($detail->exam_id == 1 && $detail->verified == 1){
                        $other_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                           if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                              $applicant->rank_points += $o_level_grades[$result->grade];
                              $subject_count += 1;

                              if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                 if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                       $o_level_pass_count += 1;
                                       $o_level_points += $o_level_grades[$result->grade];
                                    }

                                    if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                       $o_level_pass_count += 1;
                                       $other_must_subject_ready = true;
                                       $o_level_points += $o_level_grades[$result->grade];
                                    }

                                 }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                 }else{
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                       $o_level_other_pass_count += 1;
                                    }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                       $o_level_other_pass_count += 1;
                                       $o_level_points += $o_level_grades[$result->grade];
                                    }
                                 }
                              }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                 if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                       $o_level_pass_count += 1;
                                       $o_level_points += $o_level_grades[$result->grade];

                                 }
                              }else{
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                              }
                           }
                        }
                     }

                     if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects){
                     //    if(($o_level_pass_count+$o_level_must_pass_count) >= $program->entryRequirements[0]->pass_subjects && $o_level_must_pass_count >= count(unserialize($program->entryRequirements[0]->must_subjects))){

                        $programs[] = $program;
                        $o_level_selection_points[$program->id] = $o_level_points;

                     }elseif($applicant->veta_status === 1){
                        $programs[] = $program;
                     }
                  }
               }

               // Diploma
               if(str_contains($award->name,'Diploma')){
                  $o_level_pass_count = $o_level_points = $a_level_points = $diploma_gpa = 0;
                  $o_level_other_pass_count = 0;
                  $o_level_must_pass_count = 0;
                  $a_level_principle_pass_count = 0;
                  $a_level_subsidiary_pass_count = 0;
                  $diploma_major_pass_count = 0;
                  foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                     if($detail->exam_id == 1 && $detail->verified == 1){
                        $other_must_subject_ready = false;
                        foreach ($detail->results as $key => $result) {

                           if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                              $applicant->rank_points += $o_level_grades[$result->grade];
                              $subject_count += 1;

								if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                         $o_level_points += $o_level_grades[$result->grade];
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                         $o_level_pass_count += 1;
                                         $o_level_points += $o_level_grades[$result->grade];
                                         $other_must_subject_ready = true;
                                       }

                                    }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
                                         $o_level_points += $o_level_grades[$result->grade];
                                    }else{
										if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
											$o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
										}elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
											$o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
										}
									}
                                }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                        $o_level_pass_count += 1;
                                        $o_level_points += $o_level_grades[$result->grade];

                                    }
                                }else{
                                    $o_level_pass_count += 1;
                                    $o_level_points += $o_level_grades[$result->grade];
                                }
						   }
						   }

                         }elseif($detail->exam_id === 2 && $detail->verified == 1){
                           $other_advance_must_subject_ready = false;
                           $other_advance_subsidiary_ready = false;
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades[$diploma_principle_pass_grade]){
                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;

								 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_principle_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];

                                    }
                                 }else{
                                    $a_level_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
								}

                              if($a_level_grades[$result->grade] >= $a_level_grades[$subsidiary_pass_grade]){

								 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                       }

                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                         $a_level_subsidiary_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                         $other_advance_must_subject_ready = true;
                                       }

                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
                                         $a_level_points += $a_level_grades[$result->grade];
                                       }
                                    }
                                 }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                                    if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                        $a_level_subsidiary_pass_count += 1;
                                        $a_level_points += $a_level_grades[$result->grade];

                                    }
                                 }else{
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
							  }
                           }
                         }

                       }

					   if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && (($a_level_principle_pass_count > 0
						&& ($a_level_subsidiary_pass_count + $a_level_principle_pass_count >= 2)) || $a_level_principle_pass_count >= 2)){
							$programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $a_level_selection_points[$program->id] =  $a_level_points;
						}

                  $has_btc = $has_diploma = $pass_diploma = false;

                  if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level <= 4){
                     foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){
                           foreach($applicant->nacteResultDetails as $det){
                              if(str_contains(strtolower($det->programme),strtolower($sub)) && str_contains(strtolower($det->programme),'basic') && $det->verified == 1){
                                 $has_btc = true;
                                 $diploma_gpa = $det->diploma_gpa;
                              }elseif(str_contains(strtolower($det->programme),'diploma') && $det->verified == 1){
                                 $has_diploma = true;
                                 if($det->diploma_gpa >= 2){
                                    $pass_diploma = true;
                                    $diploma_gpa = $det->diploma_gpa;
                                 }
                              }
                           }
                     }
                  } elseif (unserialize($program->entryRequirements[0]->equivalent_majors) != '' && $program->entryRequirements[0]->nta_level == 5) {
                  // salim added elseif part to check nta level 5 for diploma students

                  }else{       // lupi added the else part to determine btc status when equivalent majors have not been defined
                        foreach($applicant->nacteResultDetails as $det){
                              if(str_contains(strtolower($det->programme),'basic') && $det->verified == 1){
                                 $has_btc = true;
                                 $diploma_gpa = $det->diploma_gpa;
                              }elseif(str_contains(strtolower($det->programme),'diploma') && $det->verified == 1){
                                 $has_diploma = true;
                                 if($det->diploma_gpa >= 2){
                                    $pass_diploma = true;
                                    $diploma_gpa = $det->diploma_gpa;
                                 }
                              }
                        }
                  }

                  if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_btc && !$has_diploma){
                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;

                  } elseif (($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $applicant->veta_status == 1) {
                     $programs[] = $program;

                  }elseif(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_btc && $has_diploma && $pass_diploma){
                  // retrieve campus programmes, with students, offered in the previous application window
                     $previous_programmes = CampusProgram::whereHas('students.applicant', function($query) use($applicant){$query->where('application_window_id',$applicant->application_window_id - 1);})
                                                         ->where('campus_id', $applicant->campus_id)->whereHas('program', function($query){$query->where('name','LIKE','%Ordinary%');})->get();
                     foreach($previous_programmes as $program){
                        $programs[] = $program;
                        $o_level_selection_points[$program->id] = $o_level_points;
                        $diploma_selection_grade[$program->id] = $diploma_gpa;
                     }
                  }
               }

                   // Bachelor
               if(str_contains($award->name,'Bachelor')){
               $o_level_points = $a_level_points = $diploma_gpa = null;
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
                  if($detail->exam_id == 1 && $detail->verified == 1){
                  $other_must_subject_ready = false;
                  foreach ($detail->results as $key => $result) {

                     if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                        $applicant->rank_points += $o_level_grades[$result->grade];
                        $subject_count += 1;

                        if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                           if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                              if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                              }

                              if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                                 $other_must_subject_ready = true;
                              }

                           }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                           }else{
                              if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                 $o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                              }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                 $o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                              }
                           }
                        }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                           if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                           }
                        }else{
                           $o_level_pass_count += 1;
                           $o_level_points += $o_level_grades[$result->grade];
                        }

                        if(unserialize($program->entryRequirements[0]->must_subjects) != ''){

                           if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                              if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                              }

                              if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects)) && !$other_must_subject_ready){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                                 $other_must_subject_ready = true;
                              }

                           }elseif(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                           }else{
                              if(unserialize($program->entryRequirements[0]->other_must_subjects) != '' && (count(unserialize($program->entryRequirements[0]->must_subjects)) + count(unserialize($program->entryRequirements[0]->other_must_subjects))) < $program->entryRequirements[0]->pass_subjects){
                                 $o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                              }elseif(count(unserialize($program->entryRequirements[0]->must_subjects)) < $program->entryRequirements[0]->pass_subjects && ($o_level_other_pass_count < ($program->entryRequirements[0]->pass_subjects - count(unserialize($program->entryRequirements[0]->must_subjects))))){
                                 $o_level_other_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];
                              }
                           }
                        }elseif(unserialize($program->entryRequirements[0]->exclude_subjects) != ''){
                           if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects))){
                                 $o_level_pass_count += 1;
                                 $o_level_points += $o_level_grades[$result->grade];

                           }
                        }else{
                           $o_level_pass_count += 1;
                           $o_level_points += $o_level_grades[$result->grade];
                        }
                     }
                  }
                  }elseif($detail->exam_id == 2 && $detail->verified == 1){

                     //$a_level_out_principle_pass_count = $a_level_out_subsidiary_pass_count = 0;
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
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }

                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_principle_pass_count += 1;
                                    $other_advance_must_subject_ready = true;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }else{
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }
                           }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                              if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_principle_pass_count += 1;
                                    $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                    $a_level_points += $a_level_grades[$result->grade];
                              }
                           }else{
                                 $a_level_principle_pass_count += 1;
                                 $a_level_principle_pass_points += $a_level_grades[$result->grade];
                                 $a_level_points += $a_level_grades[$result->grade];
                           }
                        }
                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){       // lupi changed to reduce the sample
                           if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                              if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }

                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                    $other_advance_must_subject_ready = true;
                                 }

                              }else{
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }
                           }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                              if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                              }
                           }else{
                              $a_level_subsidiary_pass_count += 1;
                              $a_level_points += $a_level_grades[$result->grade];
                           }
                        }
//return $result->grade.' - '.$diploma_principle_pass_grade;
                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_principle_pass_grade]){

                           $applicant->rank_points += $a_level_grades[$result->grade];
                           $subject_count += 1;
                           if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){

                              if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }

                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                    $a_level_out_principle_pass_count += 1;
                                    $other_out_advance_must_subject_ready = true;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }else{
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }else{
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                              }
                              }
                           }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                              if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_principle_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                              }
                           }else{
                                 $a_level_out_principle_pass_count += 1;
                                 $a_level_points += $a_level_grades[$result->grade];
                           }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$diploma_subsidiary_pass_grade]){

                           $applicant->rank_points += $a_level_grades[$result->grade];
                           $subject_count += 1;
                           if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                              if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }

                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_out_advance_must_subject_ready){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $other_out_advance_must_subject_ready = true;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }else{
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }else{
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                              }
                              }
                           }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                              if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_out_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                              }
                           }else{
                                 $a_level_out_subsidiary_pass_count += 1;
                                 $a_level_points += $a_level_grades[$result->grade];
                           }
                        }

                        if($a_level_grades[$result->grade] == $a_level_grades[$subsidiary_pass_grade]){

                           if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                              if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }

                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects)) && !$other_advance_must_subject_ready){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                    $other_advance_must_subject_ready = true;
                                 }

                              }else{
                                 if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];
                                 }
                              }
                           }elseif(unserialize($program->entryRequirements[0]->advance_exclude_subjects) != ''){
                              if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_exclude_subjects))){
                                    $a_level_subsidiary_pass_count += 1;
                                    $a_level_points += $a_level_grades[$result->grade];

                              }
                           }else{
                              $a_level_subsidiary_pass_count += 1;
                              $a_level_points += $a_level_grades[$result->grade];
                           }
                        }
                     }
                  }
               }

               if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                  if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                        $programs[] = $program;
                        $o_level_selection_points[$program->id] = $o_level_points;
                        $a_level_selection_points[$program->id] = $a_level_points;
                        $diploma_selection_grade[$program->id] = $diploma_gpa;
                  }
               }elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                  $programs[] = $program;
                  $o_level_selection_points[$program->id] = $o_level_points;
                  $a_level_selection_points[$program->id] = $a_level_points;
                  $diploma_selection_grade[$program->id] = $diploma_gpa;

               } elseif(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && ($applicant->veta_status == 1 || $applicant->teacher_certificate_status == 1)) {
                  $programs[] = $program;
                  $o_level_selection_points[$program->id] = $o_level_points;
                  $a_level_selection_points[$program->id] = $a_level_points;
                  $diploma_selection_grade[$program->id] = $diploma_gpa;
               }

               $has_major = false;
               $equivalent_must_subjects_count = 0;
               $diploma_gpa = null;
               $out_gpa = null;
               $has_nacte_results = false;

               foreach($applicant->nacteResultDetails as $detail){
                  if(count($detail->results) == 0 && $detail->verified == 1){
                     $has_nacte_results = true;
                     $diploma_gpa = $detail->diploma_gpa;
                  }
               }

               if(($o_level_pass_count + $o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_nacte_results && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
               }

               if(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                  foreach($applicant->nacteResultDetails as $detail){
                     if($detail->verified == 1){

                        foreach(unserialize($program->entryRequirements[0]->equivalent_majors) as $sub){

                           if(str_contains(strtolower($detail->programme),strtolower($sub))){

                                 $has_major = true;
                           }
                        }
                        $diploma_gpa = $detail->diploma_gpa;
                     }
                  }

               }else{
                  if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                     foreach($applicant->nacteResultDetails as $detail){
                        if($detail->verified == 1){
                           foreach($detail->results as $result){
                              foreach(unserialize($program->entryRequirements[0]->equivalent_must_subjects) as $sub){
                                    if(str_contains(strtolower($result->subject),strtolower($sub))){
                                       $equivalent_must_subjects_count += 1;
                                    }
                              }
                           }
                           $diploma_gpa = $detail->diploma_gpa;
                        }
                     }
                  }
               }

               if(unserialize($program->entryRequirements[0]->equivalent_majors) != '' && !$has_nacte_results){
                  // return $has_major.'-'.$o_level_pass_count.'-'.$nacte_gpa.'-'.$program->entryRequirements[0]->equivalent_gpa;
                     if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $has_major && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;

                     }
               }elseif(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != '' && !$has_nacte_results){
                  if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects &&
                        $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) &&
                        $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)  || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects &&
                        $applicant->avn_no_results === 1 && $diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
                  }
               }

               $out_pass_subjects_count = 0;
               if(unserialize($program->entryRequirements[0]->open_exclude_subjects) != '') //['OFC 017','OFP 018','OFP 020'];
               {
                  $exclude_out_subjects_codes = unserialize($program->entryRequirements[0]->open_exclude_subjects);

                  foreach($applicant->outResultDetails as $detail){
                     if($detail->verified == 1){
                        foreach($detail->results as $key => $result){
                           if(!Util::arrayIsContainedInKey($result->subject_code, $exclude_out_subjects_codes)){
                              if($out_grades[$result->grade] >= $out_grades['C']){
                                 $out_pass_subjects_count += 1;
                              }
                           }
                        }
                        $out_gpa = $detail->gpa;
                     }
                  }
               }else{
                  foreach($applicant->outResultDetails as $detail){
                     if($detail->verified == 1){
                        foreach($detail->results as $key => $result){
                           if($out_grades[$result->grade] >= $out_grades['C']){
                              $out_pass_subjects_count += 1;
                           }
                        }
                        $out_gpa = $detail->gpa;
                     }
                  }
               }

               if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                     $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $a_level_out_subsidiary_pass_count >= 1 &&
                     $a_level_out_principle_pass_count >= 1){

                  $programs[] = $program;
                  $o_level_selection_points[$program->id] = $o_level_points;
                  $a_level_selection_points[$program->id] = $a_level_points;
                  $diploma_selection_grade[$program->id] = $diploma_gpa;
                  $open_selection_grade[$program->id] = $out_gpa;
               }

               // OUT with diploma of 2.0 and above
               if(unserialize($program->entryRequirements[0]->equivalent_must_subjects) != ''){
                     if((($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                        $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $equivalent_must_subjects_count >= count(unserialize($program->entryRequirements[0]->equivalent_must_subjects)) &&
                        $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa) || ($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects &&
                        $applicant->avn_no_results === 1 && $out_pass_subjects_count >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa)){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $a_level_selection_points[$program->id] = $a_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
                     $open_selection_grade[$program->id] = $out_gpa;

                     }
               }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) != ''){
                     if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $has_major &&
                        $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $a_level_selection_points[$program->id] = $a_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
                     $open_selection_grade[$program->id] = $out_gpa;
                     }
               }elseif(unserialize($program->entryRequirements[0]->equivalent_majors) == ''){
                  if(($o_level_pass_count+$o_level_other_pass_count) >= 3 && $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa &&
                        $diploma_gpa >= $program->entryRequirements[0]->min_equivalent_gpa){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $a_level_selection_points[$program->id] = $a_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
                     $open_selection_grade[$program->id] = $out_gpa;
                     }
               }

               if(($o_level_pass_count+$o_level_other_pass_count) >= $program->entryRequirements[0]->pass_subjects && $out_pass_subjects_count >= 3 &&
                     $out_gpa >= $program->entryRequirements[0]->open_equivalent_gpa && $applicant->teacher_certificate_status === 1){

                     $programs[] = $program;
                     $o_level_selection_points[$program->id] = $o_level_points;
                     $a_level_selection_points[$program->id] = $a_level_points;
                     $diploma_selection_grade[$program->id] = $diploma_gpa;
                     $open_selection_grade[$program->id] = $out_gpa;
               }
            }
            if($subject_count != 0){
               $app = Applicant::find($applicant->id);
                  $app->rank_points = $applicant->rank_points / $subject_count;
               $app->save();
            }
        }
      }else{
         $window = $applicant->applicationWindow;
         $programs = $window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                    $query->where('award_id',$applicant->program_level_id);
            })->with(['program','campus'])->where('campus_id',session('applicant_campus_id'))->get() : [];
      }

         $data = [
            'applicant'=>$applicant,
            'campus'=>Campus::find(session('applicant_campus_id')),
            'application_window'=>$window,
            'campus_programs'=>$window ? $programs : [],
            'regulator_selection'=>false,
            'o_level_selection_points'=>$o_level_selection_points,
            'a_level_selection_points'=>$a_level_selection_points,
            'diploma_selection_grade'=>$diploma_selection_grade,
            'open_selection_grade'=>$open_selection_grade,
            'available_progs'=>$available_progs ?? [],
            'full_programs'=>$campus_progs ?? [],
            'all_programs'=>$all_programs ?? []
         ];

        return view('dashboard.application.select-programs',$data)->withTitle('Select Programmes');
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
       $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
      }else {
         if($applicant->confirmation_status == 'CANCELLED'){
               return redirect()->to('application/basic-information')->with('error','This action cannot be performed. Your admission has been cancelled');
          }
         return view('dashboard.application.upload-documents',$data)->withTitle('Upload Documents');
      }
    }

    /**
     * Upload Avn documents
     */
    public function uploadAvnDocuments(Request $request)
    {
       $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();
       $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();
       if($applicant->is_tamisemi != 1 && $applicant->is_transfered != 1){
         //check if window active
         if($applicant->program_level_id == 1 || $applicant->program_level_id == 2){
            $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
                                    ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
                                    ->where('campus_id', $applicant->campus_id)
                                    ->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                                    ->where('status', 'ACTIVE')
                                    ->latest()->first();
         }elseif($applicant->program_level_id == 4){
            $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
                                    ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
                                    ->where('campus_id', $applicant->campus_id)
                                    ->where('bsc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                                    ->where('status', 'ACTIVE')
                                    ->latest()->first();
         }elseif($applicant->program_level_id == 5){
            $application_window = ApplicationWindow::where('id', $applicant->application_window_id)
                                    ->whereHas('applicationBatches', function($query) use($applicant){ $query->where('id', $applicant->batch_id)->where('program_level_id', $applicant->program_level_id);})
                                    ->where('campus_id', $applicant->campus_id)
                                    ->where('msc_end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))
                                    ->where('status', 'ACTIVE')
                                    ->latest()->first();
         }

         if(!$application_window){
               return redirect()->to('application/submission')->with('error','Application window already closed');
          }
          if($applicant->batch_id != $batch->id){
            return redirect()->to('application/submission')->with('error','Action is not allowed at the moment');
          }
       }
       $data = [
          'applicant'=>$applicant,
          'campus'=>Campus::find(session('applicant_campus_id')),
          'regulator_selection'=>false
       ];
       return view('dashboard.application.upload-avn-documents',$data)->withTitle('Upload AVN Documents');
    }

    /**
     * Application submission
     */
    public function submission(Request $request)
      {
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->latest()->first();
        $batch = ApplicationBatch::where('application_window_id',$applicant->application_window_id)->where('program_level_id',$applicant->program_level_id)->latest()->first();

         $applicants = Applicant::where('program_level_id', $applicant->program_level_id)->where('submission_complete_status', 1)
                                ->where('application_window_id', $applicant->application_window_id)->whereNotNull('status')
                                ->where('batch_id',8)->first();
         $selection_status = !empty($applicants) ? true : false;

         $app_window = ApplicationWindow::where('campus_id', session('applicant_campus_id'))->where('status', 'ACTIVE')->first();

         if(!$app_window){
            return redirect()->back()->with('error','Application window is inactive');
         }

        if(ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
           $applicant->program_level_id)->where('begin_date','<=',  implode('-', explode('-', now()->format('Y-m-d'))))->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first()){
            if($applicant->programs_complete_status != 1){
                return redirect()->back()->with('error','You must first select programmes');
            }
        }

        if(ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
        $applicant->program_level_id)->where('begin_date','<=',  implode('-', explode('-', now()->format('Y-m-d'))))->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first()){
         if($applicant->documents_complete_status != 1 &&
            (($applicant->avn_no_results === 1 || $applicant->teacher_certificate_status === 1 || $applicant->veta_status == 1 ||
               str_contains(strtolower($applicant->programLevel->name),'masters') || (str_contains($applicant->programLevel->name,'Certificate')
               && $applicant->entry_mode == 'EQUIVALENT')))){
             return redirect()->back()->with('error','You must upload required documents');
         }
        }

        if(ApplicationBatch::where('application_window_id', $app_window->id)->where('program_level_id',
        $applicant->program_level_id)->where('begin_date','<=',  implode('-', explode('-', now()->format('Y-m-d'))))->where('end_date','>=',  implode('-', explode('-', now()->format('Y-m-d'))))->latest()->first()){
           $selection_status = $applicants != null ? true : false;
        }

        $program_selection = ApplicantProgramSelection::where('applicant_id', $applicant->id)->where('application_window_id', $applicant->application_window_id)->where('status', 'SELECTED')->first();

        //   check selection confirmation from regulator
        $regulator_status = Applicant::where('program_level_id', $applicant->program_level_id)
        ->whereHas('selections', function ($query) {$query->where('status', 'SELECTED')
        ->orWhere('status', 'PENDING');})
        ->where('application_window_id', $applicant->application_window_id)
        ->where('intake_id', $applicant->intake_id)
        ->count();

        $regulator_selection = $regulator_status != 0 ? true : false;

        $data = [
            'applicant'=>$applicant,
            'campus'=>Campus::find(session('applicant_campus_id')),
            'selected_status'=>$selection_status,
            'program_selection' => $program_selection,
            'regulator_selection'=> $regulator_selection
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

        $applicant = Applicant::find($request->get('applicant_id'));
        if($applicant->payment_complete_status == 1 && substr($request->get('phone'),1) != substr($applicant->phone,3) && $applicant->is_tamisemi != 1){
            return redirect()->back()->withInput()->with('error','The action cannot be performed at the moment');
         }
        if(Applicant::hasConfirmedResults($applicant)){
            if($applicant->first_name != $request->get('first_name') || $applicant->middle_name != $request->get('middle_name')
               || $applicant->surname != $request->get('surname') || $applicant->gender != $request->get('sex')){
                  return redirect()->back()->withInput()->with('error','The action cannot be performed at the moment');
            }
        }
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
      $applicant = Applicant::select('id')->where('id',$request->get('applicant_id'))->latest()->first();
      $student = Student::select('id')->where('applicant_id',$request->get('applicant_id'))->latest()->first();
      if(Auth::user()->hasRole('administrator')){
         $applicant_invoices = Invoice::where('payable_id',$applicant->id)->where('payable_type','applicant')->whereNull('gateway_payment_id')->get();
         foreach($applicant_invoices as $invoice){
            $invoice->payable_id = 0;
            $invoice->save();
         }

         $student_invoices = Invoice::where('payable_id',$student->id)->where('payable_type','student')->whereNull('gateway_payment_id')->get();
         foreach($student_invoices as $invoice){
            $invoice->payable_id = 0;
            $invoice->save();
         }
      }else{
         $invoices = Invoice::where('payable_id',$applicant->id)->where('payable_type','applicant')
         ->where(function($query){$query->where('control_no',null)->orWhere('control_no',0);})->get();
         foreach($invoices as $invoice){
            $invoice->payable_id = 0;
            $invoice->save();
         }
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
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program.departments',function($query) use($request){
                 $query->where('id',$request->get('department_id'));
            })->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->get();
        }elseif($request->get('duration') == 'today'){
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->where('created_at','<=',now()->subDays(1))->get();
        }elseif($request->get('gender') != null){
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->where('gender',$request->get('gender'))->get();
        }elseif($request->get('nta_level_id') != null){
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'));
            })->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->get();
        }elseif($request->get('campus_program_id') != null){
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))->whereHas('selections',function($query) use($request){
                                    $query->where('campus_program_id',$request->get('campus_program_id'));
                                   })->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->get();
        }else{
           $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->where('application_window_id',$request->get('application_window_id'))
                                    ->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->get();
        }

        if($request->get('status') == 'progress'){
/*            $applicants = Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))
                                    ->where('campus_id',$application_window->campus_id)->with(['programLevel','nectaResultDetails','nacteResultDetails'])->get();
 */

/*              $batch = ApplicationBatch::select('id')->where('application_window_id',$request->get('application_window_id'))->where('batch_no',2)->get();
            $batches = [];
            foreach($batch as $b){
               $batches[] = $b->id;
            }

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
            ->with('programLevel:id,code')->where('programs_complete_status',0)->where('application_window_id',$request->get('application_window_id'))
            ->where('campus_id',$application_window->campus_id)->whereIn('batch_id',$batch)->get(); */

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
                                    ->with('programLevel:id,code')->where('programs_complete_status',0)->where('application_window_id',$request->get('application_window_id'))
                                    ->where('campus_id',$application_window->campus_id)->get();

            /* fputcsv($file_handle, [$row->index_number,$f6_index,$avn,ucwords(strtolower($row->first_name)),ucwords(strtolower($row->middle_name)),
            ucwords(strtolower($row->surname)),$row->gender,$row->phone,$row->programLevel->code, ucwords(strtolower($row->entry_mode)), $payment_status]);
 */

        }elseif($request->get('status') == 'completed'){
/*            $applicants = Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))
                                    ->where('campus_id',$application_window->campus_id)->with(['programLevel','nectaResultDetails','nacteResultDetails'])->get();
 */
            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
            ->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number,applicant_id','nacteResultDetails:id,verified,avn,applicant_id'])->where('programs_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();

        }elseif($request->get('status') == 'submitted'){
 /*           $applicants = Applicant::where('documents_complete_status',1)->where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))
                                    ->where('campus_id',$application_window->campus_id)->with(['programLevel','nectaResultDetails','nacteResultDetails'])->get();

   */          $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
            ->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number','nacteResultDetails:id,verified,avn'])->where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }elseif($request->get('status') == 'total'){
           /*  $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)
                                    ->with(['programLevel','nectaResultDetails','nacteResultDetails'])->get(); */

            $applicants = Applicant::select('id','first_name','middle_name','surname','index_number','gender','phone','batch_id','payment_complete_status','program_level_id','entry_mode')
            ->with(['programLevel:id,code','nectaResultDetails:id,exam_id,verified,index_number','nacteResultDetails:id,verified,avn'])->where('application_window_id',$request->get('application_window_id'))->where('campus_id',$application_window->campus_id)->get();
        }
/*         foreach($applicants as $row){
         $payment_status = $row->payment_status == 1? 'Paid' : 'Not Paid';
         $f6_index = 'N/A';
         if($row->nectaResultDetails){
           foreach($row->nectaResultDetails as $detail){
              if($detail->exam_id == 2 && $detail->verified == 1){
                 $f6_index = $detail->index_number;
                 break;
              }
           }
         }
        }

        return $f6_index; */

        $batches = ApplicationBatch::select('id','batch_no')->where('application_window_id',$request->get('application_window_id'))->get();
        $callback = function() use ($applicants, $batches)
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['F.4 Index Number/EQ','F.6 Index Number/EQ','AVN','First Name','Middle Name','Surname','Gender','Phone Number','Application Level','Category','Payment Status', 'Batch#']);

                  foreach($applicants as $row){
                      $payment_status = $row->payment_complete_status == 1? 'Paid' : 'Not Paid';
                      $f6_index = null;
                      if($row->nectaResultDetails){
                        foreach($row->nectaResultDetails as $detail){
                           if($detail->exam_id == 2 && $detail->verified == 1){
                              $f6_index = $detail->index_number;
                              break;
                           }
                        }
                      }

                      $batch_no = null;
                      foreach($batches as $batch){
                         if($row->batch_id == $batch->id){
                           $batch_no = $batch->batch_no;
                           break;
                         }
                      }

                      $phone = !empty($row->phone)? substr($row->phone,3) : null;
                      $avn = null;
                      if($row->nacteResultDetails){
                        foreach($row->nacteResultDetails as $detail){
                           if($detail->verified == 1){
                              $avn = $detail->avn;
                              break;
                           }
                        }
                      }
/*                       $application_level = null;
                      if(str_contains(strtolower($row->programLevel->name), 'bachelor')){
                        $application_level = 'BD';
                      }elseif(str_contains(strtolower($row->programLevel->name), 'diploma')){

                      }elseif(str_contains(strtolower($row->programLevel->name), 'btc')){

                      }elseif(str_contains(strtolower($row->programLevel->name), 'master')){

                      }  */
                      fputcsv($file_handle, [$row->index_number,$f6_index,$avn,ucwords(strtolower($row->first_name)),ucwords(strtolower($row->middle_name)),
                              ucwords(strtolower($row->surname)),$row->gender,$phone,$row->programLevel->code, ucwords(strtolower($row->entry_mode)), $payment_status, $batch_no]);
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
        $applicant = User::find(Auth::user()->id)->applicants()->with(['insurances','programLevel'])->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
         if($applicant->confirmation_status == 'CANCELLED'){
               return redirect()->to('application/basic-information')->with('error','This action cannot be performed. Your admission has been cancelled');
          }
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
         $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
         }else {
            if($applicant->confirmation_status == 'CANCELLED'){
                  return redirect()->to('application/basic-information')->with('error','This action cannot be performed. Your admission has been cancelled');
             }
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
                return redirect()->back()->with('error','Invalid card number. Please resubmit the correct card number or request new  card.');
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
               $query->where('campus_id',$staff->campus_id);
           })->latest()->first() : null;

        }else{

            $applicant = $request->get('index_number')? Applicant::with(['nextOfKin', 'payment'])->where('index_number',$request->get('index_number'))->latest()->first() : null;

        }

        if(!$applicant && !empty($request->get('index_number'))){
            return redirect()->back()->with('error','No such applicant. Please crosscheck the index number');
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

        return view('dashboard.application.applicant-details', $data)->withTitle('Applicant Details');



     }



    /**
     * Edit applicant details
     */
    public function editApplicantDetails(Request $request)
      {


         $staff = User::find(Auth::user()->id)->staff;

        if (Auth::user()->hasRole('admission-officer')) {

            $applicant = $request->get('index_number')? Applicant::with('nextOfKin')->where('index_number',$request->get('index_number'))->where(function($query) use($staff){
               $query->where('campus_id',$staff->campus_id);
           })->latest()->first() : null;

        }else{

            $applicant = $request->get('index_number')? Applicant::with(['nextOfKin', 'payment'])->where('index_number',$request->get('index_number'))->latest()->first() : null;

        }
        if(!$applicant && !empty($request->get('index_number'))){
         return redirect()->back()->with('error','No such applicant. Please crosscheck the index number');
         }

          $data = [
         'applicant'=> $applicant,
         'awards'=>Award::all(),
		   'countries'=>Country::all(),
         'invoice'=>$request->get('index_number')? Invoice::whereNull('control_no')->orWhere('control_no',0)->where('payable_id',$applicant->id)
                                                            ->where('payable_type','applicant')->latest()->first() : null
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
            'email'=>'required|email',
            'gender'=>'required',
            'index_number'=>'required',
            'nationality'=>'required',
            'program_level_id'=>'required',
            'entry_mode'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $staff = User::find(Auth::user()->id)->staff;
        $applicant = Applicant::find($request->get('applicant_id'));
        if(Applicant::hasConfirmedResults($applicant) && $request->get('index_number') != $applicant->index_number && !Auth::user()->hasRole('administrator')){
            return redirect()->back()->with('error','The action cannot be performed');
        }

        if($applicant->payment_complete_status == 1 && $applicant->status == null && !Auth::user()->hasRole('administrator')){
            if($request->get('nationality') != $applicant->nationality || (!empty($request->get('nationality')) && $request->get('nationality') != $applicant->nationality)){
               return redirect()->back()->with('error','The action cannot be performed');
            }
        }

        if($applicant->status != null && ($request->get('entry_mode') != $applicant->entry_mode || $request->get('program_level_id') != $applicant->program_level_id) && !Auth::user()->hasRole('administrator')){
         return redirect()->back()->with('error','The action cannot be performed');
        }

/*         if(!ApplicationWindow::where('campus_id',$applicant->campus_id)->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
            return redirect()->back()->with('error','Application window already closed');
        } */
        if($applicant->submission_complete_status == 1 && ($applicant->entry_mode != $request->get('entry_mode') || $applicant->program_level_id != $request->get('program_level_id') ||
        $applicant->index_number != $request->get('index_number'))){
            return redirect()->back()->with('error','Applicant details cannot be modified because the application is already submitted');
        }
        if($applicant->submission_complete_status == 1 && $applicant->submission_complete_status == null && (
           $applicant->birth_date != DateMaker::toDBDate($request->get('dob')) ||  $applicant->nationality != $request->get('nationality') )){
            return redirect()->back()->with('error','Applicant details cannot be modified because the application is already submitted');
        }

        if($staff->campus_id == $applicant->campus_id || Auth::user()->hasRole('administrator')){
            $user = User::where('id',$applicant->user_id)->first();
            if($user->username != $request->get('index_number')){
               $user->username = $request->get('index_number');
               $user->save();
            }

            $mode_before = $applicant->entry_mode;
            $level_before = $applicant->program_level_id;
            $applicant->birth_date = DateMaker::toDBDate($request->get('dob'));
            $applicant->index_number = $request->get('index_number');
            $applicant->nationality = $request->get('nationality');
            $applicant->phone = $request->get('phone');
            $applicant->email = $request->get('email');
            $applicant->gender = $request->get('gender');
            $applicant->entry_mode = $request->get('entry_mode');
            $applicant->program_level_id = $request->get('program_level_id');
            if($request->get('program_level_id') == 4){
                $applicant->is_edited = 1;
            }
            $applicant->save();

            if($mode_before != $applicant->entry_mode || $level_before != $applicant->program_level_id){
               ApplicantProgramSelection::where('applicant_id',$applicant->id)->delete();
               Applicant::where('id',$applicant->id)->update(['programs_complete_status'=>0]);

               if($level_before != $applicant->program_level_id){
                  $batch = ApplicationBatch::where('program_level_id',$applicant->program_level_id)->where('application_window_id',$applicant->application_window_id)
                           ->latest()->first();
                  Applicant::where('id',$applicant->id)->update(['batch_id'=>$batch->id]);
               }
            }

            $award = Award::select('id','name')->find($applicant->program_level_id);
            $batch = ApplicationBatch::select('id','batch_no')->where('application_window_id',$request->get('application_window_id'))
                                     ->where('program_level_id',$award->id)->latest()->first();

            $tcu_username = $tcu_token = $nactvet_authorization_key = null;
            if($staff->campus_id == 1){
                $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
                $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
                $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KIVUKONI');

            }elseif($staff->campus_id == 2){
                $tcu_username = config('constants.TCU_USERNAME_KARUME');
                $tcu_token = config('constants.TCU_TOKEN_KARUME');
                $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_KARUME');

            }elseif($staff->campus_id == 3){
                $nactvet_authorization_key = config('constants.NACTVET_AUTHORIZATION_KEY_PEMBA');
            }
/*             if(str_contains(strtolower($award->name),'bachelor')){
               $submission_log = ApplicantSubmissionLog::where('applicant_id',$applicant->id)->where('program_level_id',$applicant->program_level_id)
                                                       ->where('application_window_id',$applicant->application_window_id)->where('batch_id',$applicant->batch_id)->first();
                  if(!empty($submission_log)){

                     $url='http://api.tcu.go.tz/applicants/resubmit';

                     $selected_programs = array();
                     $approving_selection = null;

                     foreach($applicant->selections as $selection){
                        $selected_programs[] = $selection->campusProgram->regulator_code;
                        if($selection->status == 'APPROVING'){
                              $approving_selection = $selection;

                        }
                     }

                     $f6indexno = null;
                     $otherf4indexno = $otherf6indexno = [];
                     foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 2){
                              if($f6indexno != null && $f6indexno != $detail->index_number){
                                 $otherf6indexno[] = $detail->index_number;
                              }else{
                                 $f6indexno = $detail->index_number;
                              }
                        }
                     }

                     foreach($applicant->nacteResultDetails as $detail){
                        if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                              $f6indexno = $detail->avn;
                              break;
                        }
                     }

                     foreach($applicant->nectaResultDetails as $detail) {
                        if($detail->exam_id == 1 && $detail->index_number != $applicant->index_number){
                              $otherf4indexno[]= $detail->index_number;
                        }
                     }

                     if(is_array($selected_programs)){
                        $selected_programs=implode(', ',$selected_programs);
                     }

                     if(is_array($otherf4indexno)){
                     $otherf4indexno=implode(', ',$otherf4indexno);
                     }

                     if(is_array($otherf6indexno)){
                     $otherf6indexno=implode(', ',$otherf6indexno);
                     }

                     $category = null;
                     if($applicant->entry_mode == 'DIRECT'){
                        $category = 'A';

                     }else{
                        // Open university
                        if($applicant->outResultDetails){
                              $category = 'F';

                        }

                        // Diploma holders
                        if($applicant->nacteResultDetails){
                              $category = 'D';

                        }
                     }

                  $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                  <Request>
                        <UsernameToken>
                           <Username>'.$tcu_username.'</Username>
                           <SessionToken>'.$tcu_token.'</SessionToken>
                        </UsernameToken>
                        <RequestParameters>
                           <f4indexno>'.$applicant->index_number.'</f4indexno>
                           <f6indexno>'.$f6indexno.'</f6indexno>
                           <Gender>'.$applicant->gender.'</Gender>
                           <SelectedProgrammes>'.$selected_programs.'</SelectedProgrammes>
                           <MobileNumber>'.str_replace('255', '0', $applicant->phone).'</MobileNumber>
                           <OtherMobileNumber></OtherMobileNumber>
                           <EmailAddress>'.$applicant->email.'</EmailAddress>
                           <AdmissionStatus>Provisional admission</AdmissionStatus>
                           <ProgrammeAdmitted>'.$approving_selection->campusProgram->regulator_code.'</ProgrammeAdmitted>
                           <Category>'.$category.'</Category>
                           <Reason>eligible</Reason>
                           <Nationality>'.$applicant->nationality.'</Nationality>
                           <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                           <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                           <Otherf4indexno>'.$otherf4indexno.'</Otherf4indexno>
                           <Otherf6indexno>'.$otherf6indexno.'</Otherf6indexno>
                        </RequestParameters>
                  </Request>';

                  //return $xml_request;
                  $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
                  $json = json_encode($xml_response);
                  $array = json_decode($json,TRUE);

                  if($array['Response']['ResponseParameters']['StatusCode'] == 200){
                     Applicant::where('id',$applicant->id)->update(['status'=>'SUBMITTED']);

                     $submission_log->submitted = 2;
                     $submission_log->save();

                  }else{
                     $error_log = new ApplicantFeedBackCorrection;
                     $error_log->applicant_id = $applicant->id;
                     $error_log->application_window_id = $applicant->application_window_id;
                     $error_log->programme_id = $approving_selection? $approving_selection->campusProgram->regulator_code : 'BD';
                     $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                     $error_log->remarks = $array['Response']['ResponseParameters']['StatusDescription'];
                     $error_log->save();
                  }
               }
           } */
            return redirect()->to('application/edit-applicant-details')->with('message','Applicant details updated successfully');
            //return redirect()->back()->with('message','Applicant details updated successfully');
        }
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
