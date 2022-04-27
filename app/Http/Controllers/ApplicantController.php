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
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\HealthInsurance;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Http\Controllers\NHIFService;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Utils\Util;
use Carbon\Carbon;
use App\Utils\DateMaker;
use Validator, Auth, Hash;

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
        return view('auth.applicant-login',$data)->withTitle('Student Login');
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
            'password'=>$request->get('password')
        ];

        $campus = Campus::find($request->get('campus_id'));

        $applicant = Applicant::where('index_number',$request->get('index_number'))->where('campus_id',0)->first();

        $tamisemi_applicant = Applicant::where('index_number',$request->get('index_number'))->where('is_tamisemi',1)->first();
        
        $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('campus_id',$request->get('campus_id'))->where('status','ACTIVE')->first();
        if(!$tamisemi_applicant){
          if(!$window && $applicant){
            return  redirect()->back()->with('error','Application window for '.$campus->name.' is not open.');
          }
        }

        if(Auth::attempt($credentials)){
            
            if(!Applicant::where('user_id',Auth::user()->id)->where('campus_id',$request->get('campus_id'))->first()){
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
                    $applicant->basic_info_complete_status = $app->basic_info_complete_status;
                    $applicant->next_of_kin_id = $app->next_of_kin_id;
                    $applicant->next_of_kin_complete_status = $app->next_of_kin_complete_status;
                    $applicant->birth_certificate = $app->birth_certificate;

                    $applicant->o_level_certificate = $app->o_level_certificate;

                    $applicant->a_level_certificate = $app->a_level_certificate;

                    $applicant->diploma_certificate = $app->diploma_certificate; 

                    $applicant->documents_complete_status = $app->documents_complete_status;
                    $applicant->save();
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
                        $applicant->basic_info_complete_status = $app->basic_info_complete_status;
                        $applicant->next_of_kin_id = $app->next_of_kin_id;
                        $applicant->next_of_kin_complete_status = $app->next_of_kin_complete_status;

                        $applicant->birth_certificate = $app->birth_certificate;

                        $applicant->o_level_certificate = $app->o_level_certificate;

                        $applicant->a_level_certificate = $app->a_level_certificate;

                        $applicant->diploma_certificate = $app->diploma_certificate; 

                        $applicant->documents_complete_status = $app->documents_complete_status;
                        $applicant->save();
                    }
            }
          }
            
            
            session(['applicant_campus_id'=>$request->get('campus_id')]);
            return redirect()->to('application/dashboard')->with('message','Logged in successfully');
        }else{
           return redirect()->back()->with('error','Incorrect index number or password');
        }
    }

    /**
     * Applicant dashboard
     */
    public function dashboard(Request $request)
    {
        $data = [
           'applicant'=>User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first()
        ];
        return view('dashboard.application.dashboard',$data)->withTitle('Dashboard');
    }

    /**
     * Edit basic information
     */
    public function editBasicInfo(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
        if($applicant->is_tamisemi != 1){
            if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
                 return redirect()->to('application/submission')->with('error','Application window already closed');
            }
        }
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
        
        $data = [
           'applicant'=>$applicant,
           'application_window'=>ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->first(),
           'campus'=>Campus::find(session('applicant_campus_id')),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'status_code'=>isset($array['Response'])? $array['Response']['ResponseParameters']['StatusCode'] : null,
           'wards'=>Ward::all(),
           'disabilities'=>DisabilityStatus::all(),
        ];

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
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
        if($applicant->is_tamisemi != 1){
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
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $applicant = User::find(Auth::user()->id)->applicants()->with('country')->where('campus_id',session('applicant_campus_id'))->first();
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
           'gateway_payment'=>$invoice? GatewayPayment::where('control_no',$invoice->control_no)->first() : null
        ];

        return view('dashboard.application.payments',$data)->withTitle('Payments');
    }

    /**
     * Request results
     */
    public function requestResults(Request $results)
    {
        if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             return redirect()->to('application/submission')->with('error','Application window already closed');
        }
        $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'o_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','1')->get(),
           'a_level_necta_results'=>NectaResultDetail::with('results')->where('applicant_id',$applicant->id)->where('exam_id','2')->get(),
           'nacte_results'=>NacteResultDetail::with('results')->where('applicant_id',$applicant->id) ->get()
        ];
        return view('dashboard.application.request-results',$data)->withTitle('Request Results');
    }

    /**
     * Select programs
     */
    public function selectPrograms(Request $request)
    {
        if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
             return redirect()->to('application/submission')->with('error','Application window already closed');
        }
        $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('campus_id',session('applicant_campus_id'))->first();
        $applicant = User::find(Auth::user()->id)->applicants()->with(['selections.campusProgram.program','selections'=>function($query){
                $query->orderBy('order','asc');
            },'selections.campusProgram.campus'])->where('campus_id',session('applicant_campus_id'))->first();
        $data = [
           'applicant'=>$applicant,
           'campus'=>Campus::find(session('applicant_campus_id')),
           'application_window'=>$window,
           'campus_programs'=>$window? $window->campusPrograms()->whereHas('program',function($query) use($applicant){
                   $query->where('award_id',$applicant->program_level_id);
           })->with(['program','campus'])->where('campus_id',session('applicant_campus_id'))->get() : []
        ];
        return view('dashboard.application.select-programs',$data)->withTitle('Select Programmes');
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
       $applicant = User::find(Auth::user()->id)->applicants()->with('programLevel')->where('campus_id',session('applicant_campus_id'))->first();
       if($applicant->is_tamisemi != 1){
         if(!ApplicationWindow::where('campus_id',session('applicant_campus_id'))->where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('status','ACTIVE')->first()){
               return redirect()->to('application/submission')->with('error','Application window already closed');
          }
       }
       $data = [
          'applicant'=>$applicant,
          'campus'=>Campus::find(session('applicant_campus_id')),
       ];
       return view('dashboard.application.upload-documents',$data)->withTitle('Upload Documents');
    }

    /**
     * Application submission
     */
    public function submission(Request $request)
    {
        $data = [
            'applicant'=>User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first(),
            'campus'=>Campus::find(session('applicant_campus_id')),
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
            'birth_date'=>'required',
            'phone'=>'required|min:12|max:12',
            'address'=>'required',
            'nationality'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(Carbon::now()->subYears(14)->format('Y-m-d') < Carbon::parse($request->get('birth_date'))->format('Y-m-d')){
            return redirect()->back()->with('error','Birth date must be before 14 years ago');
        }

        if(Carbon::now()->format('Y-m-d') < Carbon::parse($request->birth_date)->format('Y-m-d')){
            return redirect()->back()->with('error','Birth date cannot be the date after today');
        }


        (new ApplicantAction)->update($request);

        return Util::requestResponse($request,'Applicant updated successfully');
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
        $applicant = User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first();
        $program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
        })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
        $data = [
           'applicant'=>$applicant,
           'program_fee_invoice'=>$program_fee_invoice
        ];
        return view('dashboard.application.other-information',$data)->withTitle('Other Information');
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
     * Update Hostel Status
     */
    public function updateInsuranceStatus(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'insurance_status'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
         
         if($request->get('insurance_name') != 'NHIF' && $request->get('insurance_name') != 0){
           if(strtotime($request->get('expire_date')) <= strtotime(now())){
              return redirect()->back()->with('error','Expire date cannot be less than today\'s date');
           }
         }
         
         
         $applicant = Applicant::find($request->get('applicant_id'));
         if($request->get('insurance_name') == 'NHIF'){
            $status_code = NHIFService::checkCardStatus($request->get('card_number'))->statusCode;
            if($status_code == 406){
                return redirect()->back()->with('error','Invalid card number. Please resubmit the correct card number or request new NHIF card.');
            }
            $applicant->insurance_status = $status_code == 406? 0 : 1;
         }else{
            $applicant->insurance_status = $request->get('insurance_status');
         }
         $applicant->save();

         if($request->get('insurance_status') == 1){

             $insurance = new HealthInsurance;
             $insurance->insurance_name = $request->get('insurance_name');
             $insurance->membership_number = $request->get('card_number');
             $insurance->expire_date = DateMaker::toDBDate($request->get('expire_date'));
             $insurance->applicant_id = $applicant->id;
             $insurance->save();
         }else{
           $ac_year = StudyAcademicYear::where('status','ACTIVE')->first()->academicYear->year;
           $data = [
              'BatchNo'=>'8002217/'.$ac_year.'/001',
              'Description'=>'Batch submitted on '.date('m d, Y'),
              'CardApplications'=>[ 
                 [  
                    'CorrelationID'=>$applicant->index_number,
                    'MobileNo'=>'0'.substr($applicant->phone, 3),
                    'AcademicYear'=>$ac_year,
                    'YearOfStudy'=>1,
                    'Category'=>1
                 ]
              ]
            ];
            
            $url = 'http://196.13.105.15/OMRS/api/v1/Verification/SubmitCardApplications';
            $token = NHIFService::requestToken();

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            // For xml, change the content-type.
            curl_setopt ($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json",
              $token));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // ask for results to be returned
            // Send to remote and return data to caller.
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result);
         }

         return redirect()->back()->with('message','Health insurance status updated successfully');
    }
}
