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
use App\Domain\Academic\Models\StudyAcademicYear;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Utils\Util;
use Carbon\Carbon;
use Validator, Auth;

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

        $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->where('campus_id',$request->get('campus_id'))->first();
        if(!$window){
          return  redirect()->back()->with('error','Application window for '.$campus->name.' is not open.');
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
           'applicant'=>User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first(),
           'campus'=>Campus::find(session('applicant_campus_id')),
           'countries'=>Country::all(),
           'regions'=>Region::all(),
           'districts'=>District::all(),
           'status_code'=>$array['Response']['ResponseParameters']['StatusCode'],
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
       $data = [
          'applicant'=>User::find(Auth::user()->id)->applicants()->where('campus_id',session('applicant_campus_id'))->first(),
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
            'street'=>'required'
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
}
