<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Department;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Models\Campus;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\AdmissionAttachment;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Actions\ApplicantAction;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;
use App\Utils\SystemLocation;
use App\Mail\AdmissionLetterCreated;
use Validator, Hash, Config, Auth, PDF, Mail;

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
        if($request->get('department_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program.departments',function($query) use($request){
                 $query->where('id',$request->get('department_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }elseif($request->get('duration') == 'TODAY'){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('created_at','<=',now()->subDays(1))->paginate(20);
        }elseif($request->get('gender') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->where('gender',$request->get('gender'))->paginate(20);
        }elseif($request->get('nta_level_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }elseif($request->get('campus_program_id') != null){
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->whereHas('selections',function($query) use($request){
                 $query->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake'])->paginate(20);
        }else{
           $applicants = Applicant::where('application_window_id',$request->get('application_window_id'))->with(['nextOfKin','intake'])->paginate(20);
        }
        $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::all(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'nta_levels'=>NTALevel::all(),
            'departments'=>Department::all(),
            'campus_programs'=>CampusProgram::with('program')->get(),
            'applicants'=>$applicants,
            'request'=>$request
        ];
        return view('dashboard.application.applicants-list',$data)->withTitle('Applicants');
    }

    /**
     * Selected applicants
     */
    public function selectedApplicants(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if($request->get('query')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->paginate(20);
         }elseif($request->get('gender')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->paginate(20);
         }elseif($request->get('campus_program_id')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }elseif($request->get('nta_level_id')){
             $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'))->where('status','APPROVING');
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }else{
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }
         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'request'=>$request
         ];
         return view('dashboard.application.selected-applicants',$data)->withTitle('Selected Applicants');
    }

    /**
     * Download selected applicants list
     */
    public function downloadSelectedApplicants(Request $request)
    {

        $staff = User::find(Auth::user()->id)->staff;
        $award = Award::find($request->get('program_level_id'));
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Selected-Applicants-'.$award->name.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
         
         ApplicationWindow::find($request->get('application_window_id'))->update(['enrollment_report_download_status'=>1]);

         if($request->get('gender')){
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('campus_program_id')){
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }elseif($request->get('nta_level_id')){
             $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'))->where('status','APPROVING');
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }else{
            $list = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();
         }

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['First Name','Middle Name','Surname','Gender','Programme','Status']);
                  foreach ($list as $applicant) { 

                      foreach ($applicant->selections as $select) {
                         if($select->status == 'APPROVING'){
                            $selection = $select;
                         }
                      }

                      fputcsv($file_handle, [$applicant->first_name,$applicant->middle_name,$applicant->surname,$applicant->gender,$selection->campusProgram->program->name, $selection->status
                        ]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }

    /**
     * Submit selected applicants
     */
    public function submitSelectedApplicants(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program','nectaResultDetails'])->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id)->get();


            foreach($applicants as $applicant){

                 if($request->get('applicant_'.$applicant->id) == $applicant->id){
                 //$url='https://api.tcu.go.tz/applicants/submitProgramme';

                   $url='http://41.59.90.200/applicants/submitProgramme';
                   
                   $selected_programs = array();
                   foreach($applicant->selections as $selection){
                       $selected_programs[] = $selection->campusProgram->regulator_code;
                       if($selection->status == 'APPROVING'){
                           $approving_selection = $selection;
                       }
                   }

                   $f6indexno = null;
                   foreach ($applicant->nectaResultDetails as $detail) {
                       if($detail->exam_id == 2){
                          $f6indexno = $detail->index_number;
                       }
                   }

                   if($f6indexno){

                 $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                  <Request>
                  <UsernameToken>
                  <Username>'.config('constants.TCU_USERNAME').'</Username>
                  <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                  </UsernameToken>
                  <RequestParameters>
                  <f4indexno>'.$applicant->index_number.'</f4indexno >
                  <f6indexno>'.$f6indexno.'</f6indexno>
                  <SelectedProgrammes>'.implode(',', $selected_programs).'</SelectedProgrammes>
                  <MobileNumber>'.str_replace('-', '', $applicant->phone).'</MobileNumber>
                  <OtherMobileNumber></OtherMobileNumber>
                  <EmailAddress>'.$applicant->email.'</EmailAddress>
                  <Category>A</Category>
                  <AdmissionStatus>provisional admission</AdmissionStatus>
                  <ProgrammeAdmitted>'.$approving_selection->campusProgram->regulator_code.'</ProgrammeAdmitted>
                  <Reason>eligible</Reason>
                  <Nationality >'.$applicant->nationality.'</Nationality>
                  <Impairment>'.$applicant->disabilityStatus->name.'</Impairment>
                  <DateOfBirth>'.$applicant->birth_date.'</DateOfBirth>
                  <NationalIdNumber>'.$applicant->nin.'</NationalIdNumber>
                  <Otherf4indexno></Otherf4indexno>
                  <Otherf6indexno></Otherf6indexno>
                  </RequestParameters>
                  </Request>';
            $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
            $json = json_encode($xml_response);
            $array = json_decode($json,TRUE);

            $select = ApplicantProgramSelection::find($approving_selection->id);
            $select->status = 'SELECTED';
            $select->save();

            return dd($array);
              }


            }

          }

        return redirect()->back()->with('message','Applicants submitted to TCU successfully');
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
     * Select program
     */
    public function selectProgram(Request $request)
    {   
        $count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count();

        $similar_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->where('campus_program_id',$request->get('campus_program_id'))->count();
        if($similar_count == 0){
             if($count >= 4){
                return redirect()->back()->with('error','You cannot select more than 4 programmes');
             }else{
                 $selection = new ApplicantProgramSelection;
                 $selection->applicant_id = $request->get('applicant_id');
                 $selection->campus_program_id = $request->campus_program_id;
                 $selection->application_window_id = $request->get('application_window_id');
                 $selection->order = $request->get('choice');
                 $selection->save();

                 $select_count = ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count();
                 if($select_count == 3){
                    $applicant = Applicant::find($request->get('applicant_id'));
                    $applicant->programs_complete_status = 1;
                    $applicant->save();
                 }

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
        $validation = Validator::make($request->all(),[
            'document'=>'required|mimes:pdf,png,jpeg,jpg'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new ApplicantAction)->uploadDocuments($request);

        return redirect()->back()->with('message','Document uploaded successfully');
    }

    /**
     * Delete uploaded document
     */
    public function deleteDocument(Request $request)
    {
        $applicant = Applicant::with('programLevel')->where('user_id',Auth::user()->id)->where('campus_id',session('applicant_campus_id'))->first();
        if($request->get('name') == 'birth_certificate'){
           unlink(public_path().'/uploads/'.$applicant->birth_certificate);
           $applicant->birth_certificate = null;
        }

        if($request->get('name') == 'o_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->o_level_certificate);
           $applicant->o_level_certificate = null;
        }

        if($request->get('name') == 'a_level_certificate'){
           unlink(public_path().'/uploads/'.$applicant->a_level_certificate);
           $applicant->a_level_certificate = null;
        }

        if($request->get('name') == 'diploma_certificate'){
           unlink(public_path().'/uploads/'.$applicant->diploma_certificate);
           $applicant->diploma_certificate = null;
        }

        if($applicant->entry_mode == 'DIRECT'){
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }
        }else{
            if(str_contains($applicant->programLevel->name,'Bachelor')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->diploma_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->passport_picture){
                    $applicant->documents_complete_status = 1;
                }else{
                    $applicant->documents_complete_status = 0;
                }
            }
        }

        $applicant->save();


        return redirect()->back()->with('message','File deleted successfully');
    }

    /**
     * Download application summary
     */
    public function downloadSummary(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicants()->with(['nextOfKin.country','nextOfKin.region','nextOfKin.district','nextOfKin.ward','country','region','district','ward','disabilityStatus','nectaResultDetails.results','nacteResultDetails.results'])->where('campus_id',session('applicant_campus_id'))->first();
        $data = [
           'applicant'=>$applicant,
           'selections'=>ApplicantProgramSelection::with(['campusProgram.program'])->where('applicant_id',$applicant->id)->get()
        ];
        $pdf = PDF::loadView('dashboard.application.summary', $data)->setPaper('a4','portrait');
        return $pdf->stream();
    }

    /**
     * Submit application
     */
    public function submitApplication(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'agreement_check'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

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
       if($applicant->programs_complete_status == 0){
          return redirect()->back()->with('error','Programmes selection section not completed');
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
        $invoice->reference_no = 'MNMA-'.time();
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
        $inst_id = Config::get('constants.SUBSPCODE');

        $email = $payable->email? $payable->email : 'application@mnma.ac.tz';

        return $this->requestControlNumber($request,
                                  $invoice->reference_no,
                                  $inst_id,
                                  $invoice->amount,
                                  $fee_type->description,
                                  $fee_type->gfs_code,
                                  $fee_type->payment_option,
                                  $payable->id,
                                  $payable->first_name.' '.$payable->surname,
                                  $payable->phone,
                                  $email,
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
            'index_number'=>'required|unique:applicants',
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

        if($usr = User::where('username',$request->get('index_number'))->where('password',Hash::make($request->get('password')))->first()){
            $user = $usr;
        }else{
            $user = new User;
            $user->username = $request->get('index_number');
            $user->password = Hash::make($request->get('password'));
            $user->save();
        }

        $role = Role::where('name','applicant')->first();
        $user->roles()->sync([$role->id]);

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
        
        return redirect()->to('application/login')->with('message','Applicant registered successfully');

    }

    /**
     * Display run selection page
     */
    public function showRunSelection(Request $request)
    { 
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
           'staff'=>$staff,
           'awards'=>Award::all(),
           'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
           'request'=>$request
        ];
        return view('dashboard.application.run-selection',$data)->withTitle('Run Selection');
    }

    /**
     * Run application selection
     */
    public function runSelection(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $staff = User::find(Auth::user()->id)->staff;
        // Phase I
        $campus_programs = CampusProgram::whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('award_id'));
        })->whereHas('entryRequirements',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->with(['entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->where('campus_id',$staff->campus_id)->get();

        foreach($campus_programs as $program){
           $count[$program->id] = 0;
        }

        $award = Award::find($request->get('award_id'));

        $applicants = Applicant::whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->get();


        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $selected_program = array();
        
        foreach($applicants as $applicant){
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
           }else{
             $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'S'=>0.5,'F'=>0];
           }
           $selected_program[$applicant->id] = false;
           $subject_count = 0;
           foreach($applicant->selections as $selection){
              foreach($campus_programs as $program){
                
                if($program->id == $selection->campus_program_id){

                  if(count($program->entryRequirements) == 0){
                    return redirect()->back()->with('error',$program->program->name.' does not have entry requirements');
                  }

                   // Certificate
                   if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {
                          
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
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
                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }
                   }

                   // Diploma
                   if(str_contains($award->name,'Diploma')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_subsidiary_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                $applicant->rank_points += $o_level_grades[$result->grade];
                                $subject_count += 1;


                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
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
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
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
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->advance_other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
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
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($a_level_subsidiary_pass_count >= 1 && $a_level_principle_pass_count >= 1)){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                         }

                         
                       }
                       $has_btc = false;
                       foreach($applicant->nacteResultDetails as $detailKey=>$detail){
                          if(str_contains($detail->programme,'BASIC TECHNICIAN CERTIFICATE')){
                              $has_btc = true;
                          }
                       }

                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                       }
                   }
                   
                   // Bachelor
                   if(str_contains($award->name,'Bachelor')){
                       $o_level_pass_count = 0;
                       $a_level_principle_pass_count = 0;
                       $a_level_principle_pass_points = 0;
                       $a_level_subsidiary_pass_count = 0;
                       $diploma_pass_count = 0;
                       
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {

                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

                                 $applicant->rank_points += $o_level_grades[$result->grade];
                                 $subject_count += 1;

                                 if(unserialize($program->entryRequirements[0]->must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_must_subjects))){
                                         $o_level_pass_count += 1;
                                       }
                                    }else{
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->must_subjects))){
                                         $o_level_pass_count += 1;
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
                           foreach ($detail->results as $key => $result) {

                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

                                 $applicant->rank_points += $a_level_grades[$result->grade];
                                 $subject_count += 1;
                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_principle_pass_count += 1;
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
                                    }
                                 }
                              }
                              if($a_level_grades[$result->grade] == $a_level_grades['S']){

                                 if(unserialize($program->entryRequirements[0]->advance_must_subjects) != ''){
                                    if(unserialize($program->entryRequirements[0]->other_advance_must_subjects) != ''){
                                       if(in_array($result->subject_name, unserialize($program->entryRequirements[0]->advance_must_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                         $a_level_subsidiary_pass_count += 1;
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
                                 }
                              }
                           }
                         }

                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $a_level_principle_pass_count >= 2 && $a_level_principle_pass_points >= $program->entryRequirements[0]->principle_pass_points){

                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }

                       foreach ($applicant->nacteResultDetails as $detailKey=>$detail) {
                         foreach ($detail->results as $key => $result) {
                              if($diploma_grades[$result->grade] >= $diploma_grades[$program->entryRequirements[0]->equivalent_average_grade]){
                                 $diploma_pass_count += 1;
                              }
                           }
                         if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && ($diploma_pass_count >= $program->entryRequirements[0]->equivalent_pass_subjects || $detail->diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa)){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }
                   }
                }
              }
           }
           if($subject_count != 0){
              $applicant->rank_points = $applicant->rank_points / $subject_count;
           }
           $applicant->save();
        }

        // Phase II
        $choices = array(1,2,3,4);
        $applicants = Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'))->where('status','ELIGIBLE');
        })->get();

        for($i = 0; $i < count($applicants); $i++){
            for($j = $i + 1; $j < count($applicants); $j++){
               if($applicants[$i]->rank_points < $applicants[$j]->rank_points){
                 $temp = $applicants[$i];
                 $applicants[$i] = $applicants[$j];
                 $applicants[$j] = $temp;
               }
            }
        }
        
        foreach($choices as $choice){   
            foreach ($campus_programs as $program) {

                if(isset($program->entryRequirements[0])){
                foreach($applicants as $applicant){
                  
                  foreach($applicant->selections as $selection){
                     if($selection->order == $choice && $selection->campus_program_id == $program->id){
                        if($count[$program->id] < $program->entryRequirements[0]->max_capacity && $selection->status == 'ELIGIBLE' && !$selected_program[$applicant->id]){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'APPROVING';
                           $select->status_changed_at = now();
                           $select->save();

                           $selected_program[$applicant->id] = true;

                           $count[$program->id]++;
                        }
                     }
                  }
                }
              }
           }
        }

        return redirect()->back()->with('message','Selection run successfully');
    }

    /**
     * Admit applicant
     */
    public function admitApplicant(Request $request, $applicant_id, $selection_id)
    {
        $data = [
           'applicant'=>Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results','disabilityStatus'])->find($applicant_id),
           'selection'=>ApplicantProgramSelection::with(['campusProgram.program'])->find($selection_id)
        ];
        return view('dashboard.application.applicant-admission',$data)->withTitle('Applicant Admission');
    }

    /**
     * Register applicant
     */
    public function registerApplicant(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'academic_results_check'=>'required',
            'fee_payment_check'=>'required',
            'insurance_check'=>'required',
            'documents_check'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $applicant = Applicant::find($request->get('applicant_id'));
        $applicant->academic_results_check = 1;
        $applicant->payment_check = 1;
        $applicant->insurance_check = 1;
        $applicant->documents_check = 1;
        $applicant->save();

        $studentship_status = StudentshipStatus::where('status','ACTIVE')->first();

        if($stud = Student::where('applicant_id',$applicant->id)->first()){
            $student = $stud;
        }else{
            $student = new Student;
        }
        $student->applicant_id = $applicant->id;
        $student->first_name = $applicant->first_name;
        $student->middle_name = $applicant->middle_name;
        $student->surname = $applicant->surname;
        $student->user_id = $applicant->user_id;
        $student->gender = $applicant->gender;
        $student->phone = $applicant->phone;
        $student->email = $applicant->email;
        $student->birth_date = $applicant->birth_date;
        $student->nationality = $applicant->nationality;
        $student->year_of_study = 1;
        $student->registration_number = 'MNMA';
        $student->disability_status_id = $applicant->disability_status_id;
        $student->studentship_status_id = $studentship_status->id;
        $student->save();

        return redirect()->back()->with('message','Applicant registered as student successfully');
    }

    /**
     * Selected applicants
     */
    public function applicantsRegistration(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if($request->get('query')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->paginate(20);
         }elseif($request->get('gender')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->where('gender',$request->get('gender'))->paginate(20);
         }elseif($request->get('campus_program_id')){
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED')->where('campus_program_id',$request->get('campus_program_id'));
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }elseif($request->get('nta_level_id')){
             $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections.campusProgram.program',function($query) use($request){
                 $query->where('nta_level_id',$request->get('nta_level_id'))->where('status','SELECTED');
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }else{
            $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','SELECTED');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         }
         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'request'=>$request
         ];
         return view('dashboard.application.applicants-registration',$data)->withTitle('Applicants Registration');
    }

        /**
     * Selected applicants
     */
    public function applicantsAdmission(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('selections',function($query) use($request){
             $query->where('status','SELECTED');
        })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20);
         
         $data = [
            'staff'=>$staff,
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'nta_levels'=>NTALevel::all(),
            'selected_applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->get(),
            'campus_programs'=>CampusProgram::whereHas('selections',function($query) use($request){
                  $query->where('application_window_id',$request->get('application_window_id'));
            })->with('program')->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>$applicants,
            'request'=>$request
         ];
         return view('dashboard.application.applicants-admission',$data)->withTitle('Applicants Admission');
    }

    /**
     * Show upload admission attachment
     */
    public function uploadAttachments(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         $data = [
            'staff'=>$staff,
            'attachments'=>AdmissionAttachment::paginate(20),
            'application_windows'=>ApplicationWindow::where('campus_id',$staff->campus_id)->get(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'awards'=>Award::all(),
            'request'=>$request
         ];
         return view('dashboard.application.upload-attachments',$data)->withTitle('Upload Attachments');
    }

    /**
     * Upload admission attachments
     */
    public function uploadAttachmentFile(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name'=>'required',
            'attachment'=>'required|mimes:pdf'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if($request->hasFile('attachment')){
            $destination = SystemLocation::uploadsDirectory();
            $request->file('attachment')->move($destination, $request->file('attachment')->getClientOriginalName());


            $attachment = new AdmissionAttachment;
            $attachment->name = $request->get('name');
            $attachment->file_name = $request->file('attachment')->getClientOriginalName();
            $attachment->save();
        }

        return redirect()->back()->with('message','Attachment uploaded successfully');
    }

    /**
     * Download admission attachment
     */
    public function downloadAttachment(Request $request)
    {
        $attachment = AdmissionAttachment::find($request->get('id'));
        return response()->download(public_path().'/uploads/'.$attachment->file_name);
    }

    /**
     * Delete admission attachment
     */
    public function deleteAttachment(Request $request)
    {
        $attachment = AdmissionAttachment::find($request->get('id'));
        if(file_exists(public_path().'/uploads/'.$attachment->file_name)){
           unlink(public_path().'/uploads/'.$attachment->file_name);
        }
        $attachment->delete();
        return redirect()->back()->with('message','Attachment deleted successfully');
    }

    /**
     * Send admission letter to applicants
     */
    public function sendAdmissionLetter(Request $request)
    {
        $applicants = Applicant::whereHas('intake.applicationWindows',function($query) use($request){
             $query->where('id',$request->get('application_window_id'));
        })->whereHas('selections',function($query) use($request){
             $query->where('status','SELECTED');
        })->with(['nextOfKin','intake','selections'=>function($query){
             $query->where('status','SELECTED');
        },'selections.campusProgram.program','applicationWindow'])->where('program_level_id',$request->get('program_level_id'))->get();

        foreach($applicants as $applicant){
           // try{
               $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
               $ac_year += 1;
               $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
                      $query->where('year','LIKE','%'.$ac_year.'%');
                })->first();
               if(!$study_academic_year){
                   return redirect()->back()->with('error','Admission study academic year not created');
               }

               $data = [
                 'applicant'=>$applicant,
                 'program_name'=>$applicant->selections[0]->campusProgram->program->name,
                 'study_year'=>$study_academic_year->academicYear->year
               ];
               $pdf = PDF::loadView('dashboard.application.reports.admission-letter',$data);
               return $pdf->stream();
               $user = new User;
               $user->email = 'amanighachocha@gmail.com'; //$applicant->email;
               $user->username = $applicant->first_name.' '.$applicant->surname;
               Mail::to($user)->send(new AdmissionLetterCreated($applicant,$study_academic_year, $pdf));
           // }catch(\Exception $e){}
        }
    }


    /**
     * Show dashboard
     */
    public function showDashboard(Request $request)
    {
         $staff = User::find(Auth::user()->id)->staff;
         if(!Auth::user()->hasRole('adminstrator')){
           $application_window = ApplicationWindow::where('status','ACTIVE')->where('campus_id',$staff->campus_id)->first();
         }else{
           $application_window = null;
         }
         $data = [
            'application_windows'=>ApplicationWindow::with(['campus','intake'])->get(),
            'campuses'=>Campus::all(),
            'progress_applications'=>$application_window? Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->count() : Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->count(),
            'completed_applications'=>$application_window? Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->count() : Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->count(),
            'submitted_applications'=>$application_window? Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->count() : Applicant::where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->count(),
            'total_applications'=>$application_window? Applicant::where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->count() : Applicant::where('application_window_id',$request->get('application_window_id'))->count(),
            'today_progress_applications'=>$application_window? Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count() : Applicant::where('documents_complete_status',0)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'today_completed_applications'=>$application_window? Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count() : Applicant::where('documents_complete_status',1)->where('submission_complete_status',0)->where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'today_submitted_applications'=>$application_window? Applicant::where('submission_complete_status',1)->where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count() : Applicant::where('submission_complete_status',1)->where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'today_total_applications'=>$application_window? Applicant::where('application_window_id',$application_window->id)->where('campus_id',$staff->campus_id)->whereDate('created_at','=',now()->format('Y-m-d'))->count() : Applicant::where('application_window_id',$request->get('application_window_id'))->whereDate('created_at','=',now()->format('Y-m-d'))->count(),
            'staff'=>$staff,
            'request'=>$request
         ];
         return view('dashboard.application.application-dashboard',$data)->withTitle('Application Dashboard');
    }
}
