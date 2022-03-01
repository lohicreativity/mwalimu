<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\Applicant;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Actions\ApplicantAction;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;
use Validator, Hash, Config, Auth;

class ApplicationController extends Controller
{
    /**
     * Disaplay form for application
     */
    public function index(Request $request)
    {
    	$data = [
           'awards'=>Award::all(),
           'intakes'=>Intake::all()
    	];
    	return view('dashboard.application.register',$data)->withTitle('Applicant Registration');
    }


    /**
     * Show applicants list
     */
    public function showApplicantsList(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::all(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->with(['nextOfKin','intake'])->paginate(20)
        ];
        return view('dashboard.application.applicants-list',$data)->withTitle('Applicants');
    }

    /**
     * Select program
     */
    public function selectProgram(Request $request)
    {   
        $count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count();

        $similar_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('campus_program_id',$request->get('campus_program_id'))->count();
        if($similar_count == 0){
             if($count >= 3){
                return redirect()->back()->with('error','You cannot select more than 3 programmes');
             }else{
                 $selection = new ApplicantProgramSelection;
                 $selection->applicant_id = $request->get('applicant_id');
                 $selection->campus_program_id = $request->campus_program_id;
                 $selection->application_window_id = $request->get('application_window_id');
                 $selection->order = $request->get('choice');
                 $selection->save();

                 return redirect()->back()->with('message','Programme selected successfully');
             }
        }else{
           return redirect()->back()->with('error','Programme already selected');
        }
    }

    /**
     * Reset program selection 
     */
    public function resetProgramSelection($id)
    {
        try{
          $selection = ApplicantProgramSelection::findOrFail($id);
          $selection->delete();
          return redirect()->back()->with('message','Selection reset successfully');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Upload documents
     */
    public function uploadDocuments(Request $request)
    {
        (new ApplicantAction)->uploadDocuments($request);

        return redirect()->back()->with('message','Document uploaded successfully');
    }

    /**
     * Delete uploaded document
     */
    public function deleteDocument(Request $request)
    {
        $applicant = Applicant::where('user_id',Auth::user()->id)->first();
        if($request->get('name') == 'birth_certificate'){
           unlink(public_path().'/uploads/'.$applicant->birth_certificate);
           $applicant->birth_certificate = null;
           $applicant->save();
        }

        if($request->get('name') == 'o_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->o_level_certificate);
           $applicant->o_level_certificate = null;
           $applicant->save();
        }

        if($request->get('name') == 'a_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->a_level_certificate);
           $applicant->a_level_certificate = null;
           $applicant->save();
        }

        if($request->get('name') == 'diploma_certificate'){
           unlink(public_path().'/uploads/'.$applicant->diploma_certificate);
           $applicant->diploma_certificate = null;
           $applicant->save();
        }


        return redirect()->back()->with('message','File deleted successfully');
    }

    /**
     * Submit application
     */
    public function submitApplication(Request $request)
    {
       $applicant = Applicant::find($request->get('applicant_id'));
       if($applicant->basic_info_complete_status == 0){
          return redirect()->back()->with('error','Basic information section not completed');
       }
       if($applicant->next_of_kin_complete_status == 0){
          return redirect()->back()->with('error','Next of kin section not completed');
       }
       if($applicant->payment_complete_status == 0){
          return redirect()->back()->with('error','Payment section not completed');
       }
       if($applicant->results_complete_status == 0){
          return redirect()->back()->with('error','Results section not completed');
       }
       if($applicant->documents_complete_status == 0){
          return redirect()->back()->with('error','Upload documents section not completed');
       }
       $applicant->submission_complete_status = 1;
       $applicant->save();
       return redirect()->back()->with('message','Application Submitted Successfully');
    }

    /**
     * Request control number 
     */
    public function getControlNumber(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'fee_amount_id'=>'required',
            'applicant_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $applicant = Applicant::with('country')->find($request->get('applicant_id'));
        $fee_amount = FeeAmount::with(['feeItem.feeType'])->find($request->get('fee_amount_id'));

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.$applicant->index_number.'-'.time();
        if($applicant->country->code == 'TZ'){
           $invoice->amount = $fee_amount->amount_in_tzs;
           $invoice->currency = 'TZS';
        }else{
           $invoice->amount = $fee_amount->amount_in_usd;
           $invoice->currency = 'USD';
        }
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $fee_amount->feeItem->fee_type_id;
        $invoice->save();


        $payable = Invoice::find($invoice->id)->payable;
        $fee_type = $fee_amount->feeItem->feeType;

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = Config::get('constants.SPCODE');

        return $this->requestControlNumber($request,
                                  $invoice->reference_no,
                                  $inst_id,
                                  $invoice->amount,
                                  $fee_type->description,
                                  $fee_type->gfs_code,
                                  $fee_type->payment_option,
                                  $payable->id,
                                  $payable->first_name.' '.$payable->middle_name.' '.$payable->surname,
                                  $payable->phone,
                                  $payable->email,
                                  $generated_by,
                                  $approved_by,
                                  $fee_type->duration,
                                  $invoice->currency);
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
     * Store registration information
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'first_name'=>'required',
            'surname'=>'required',
            'index_number'=>'required|unique:applicants_old',
            'entry_mode'=>'required',
            'password'=>'required|min:8'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $user = new User;
        $user->username = $request->get('index_number');
        $user->password = Hash::make($request->get('password'));
        $user->save();

        $role = Role::where('name','applicant')->first();
        $user->roles->sync([$role->id]);

        $applicant = new Applicant;
        $applicant->first_name = $request->get('first_name');
        $applicant->middle_name = $request->get('middle_name');
        $applicant->surname = $request->get('surname');
        $applicant->user_id = $user->id;
        $applicant->index_number = $request->get('index_number');
        $applicant->entry_mode = $request->get('entry_mode');
        $applicant->program_level_id = $request->get('program_level_id');
        $applicant->intake_id = $request->get('intake_id');
        $applicant->save();
        
        return redirect()->back()->with('message','Applicant registered successfully');

    }
}
