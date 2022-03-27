<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\Intake;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Department;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Application\Models\ApplicationWindow;
use App\Domain\Application\Models\ApplicantProgramSelection;
use App\Domain\Application\Actions\ApplicantAction;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Role;
use Validator, Hash, Config, Auth, PDF;

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
         $data = [
            'staff'=>User::find(Auth::user()->id)->staff,
            'application_windows'=>ApplicationWindow::all(),
            'awards'=>Award::all(),
            'application_window'=>ApplicationWindow::find($request->get('application_window_id')),
            'applicants'=>Applicant::whereHas('intake.applicationWindows',function($query) use($request){
                 $query->where('id',$request->get('application_window_id'));
            })->whereHas('selections',function($query) use($request){
                 $query->where('status','APPROVING');
            })->with(['nextOfKin','intake','selections.campusProgram.program'])->where('program_level_id',$request->get('program_level_id'))->paginate(20),
            'request'=>$request
         ];
         return view('dashboard.application.selected-applicants',$data)->withTitle('Selected Applicants');
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
     * Download application summary
     */
    public function downloadSummary(Request $request)
    {
        $applicant = User::find(Auth::user()->id)->applicant()->with(['nextOfKin.country','nextOfKin.region','nextOfKin.district','nextOfKin.ward','country','region','district','ward','disabilityStatus'])->first();
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
        $invoice->reference_no = 'MNMA-'.str_replace('/', '-', $applicant->index_number).'-'.time();
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

        $window = ApplicationWindow::where('begin_date','<=',now()->format('Y-m-d'))->where('end_date','>=',now()->format('Y-m-d'))->first();

        if(!$window){
          return  redirect()->back()->with('error','No open application window at the moment');
        }

        $user = new User;
        $user->username = $request->get('index_number');
        $user->password = Hash::make($request->get('password'));
        $user->save();

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
        $applicant->application_window_id = $window->id;
        $applicant->save();
        
        return redirect()->to('application/login')->with('message','Applicant registered successfully');

    }

    /**
     * Display run selection page
     */
    public function showRunSelection(Request $request)
    {
        $data = [
           'staff'=>User::find(Auth::user()->id)->staff,
           'awards'=>Award::all(),
           'application_windows'=>ApplicationWindow::all(),
           'application_window'=>ApplicationWindow::find($request->get('application_window_id'))
        ];
        return view('dashboard.application.run-selection',$data)->withTitle('Run Selection');
    }

    /**
     * Run application selection
     */
    public function runSelection(Request $request)
    {
        // Phase I
        $campus_programs = CampusProgram::whereHas('program',function($query) use($request){
             $query->where('award_id',$request->get('award_id'));
        })->with(['entryRequirements'=>function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        }])->get();

        $award = Award::find($request->get('award_id'));

        $applicants = Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'));
        })->get();

        $o_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5,'F'=>0];

        $diploma_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'F'=>0];

        $selected_program = array();
        
        foreach($applicants as $applicant){
           $index_number = $applicant->index_number;
           $exam_year = explode('/', $index_number)[2];

           if($exam_year < 2014 || $exam_year > 2015){
             $a_level_grades = ['A'=>5,'B'=>4,'C'=>3,'D'=>2,'E'=>1,'S'=>0.5];
           }else{
             $a_level_grades = ['A'=>5,'B+'=>4,'B'=>3,'C'=>2,'D'=>1,'E'=>0.5];
           }
           $selected_program[$applicant->id] = false;
           foreach($applicant->selections as $selection){
              foreach($campus_programs as $program){
                if($program->id == $selection->campus_program_id && isset($program->entryRequirements[0])){
                   // Certificate
                   if(str_contains($award->name,'Certificate')){
                       $o_level_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {
                         if($detail->exam_id == 1){
                           foreach ($detail->results as $key => $result) {
                              if($o_level_grades[$result->grade] >= $o_level_grades[$program->entryRequirements[0]->pass_grade]){

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
                                 }
                              }
                           }
                         }elseif($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {
                              if($a_level_grades[$result->grade] >= $a_level_grades['E']){

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
                              $has_btc = 1;
                          }
                       }

                       if($o_level_pass_count >= $program->entryRequirements[0]->pass_subjects && $has_btc){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                       }
                   }

                   // Certificate
                   if(str_contains($award->name,'Bachelor')){
                       $o_level_pass_count = 0;
                       $a_level_pass_count = 0;
                       $diploma_pass_count = 0;
                       foreach ($applicant->nectaResultDetails as $detailKey=>$detail) {

                           if($detail->exam_id == 2){
                           foreach ($detail->results as $key => $result) {
                              if($a_level_grades[$result->grade] >= $program->entryRequirements[0]->principle_pass_points){
                                 if(!in_array($result->subject_name, unserialize($program->entryRequirements[0]->exclude_subjects)) || in_array($result->subject_name, unserialize($program->entryRequirements[0]->principle_subjects)) && in_array($result->subject_name, unserialize($program->entryRequirements[0]->other_advance_must_subjects))){
                                   $a_level_pass_count += 1;
                                 }
                              }
                           }
                         }
                         if($a_level_pass_count >= $program->entryRequirements[0]->principle_pass_subjects && count($applicant->nectaResultDetails) == ($detailKey+1)){
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
                         if($diploma_pass_count >= $program->entryRequirements[0]->equivalent_pass_subjects && $detail->diploma_gpa >= $program->entryRequirements[0]->equivalent_gpa && count($applicant->nacteResultDetails) == ($detailKey+1)){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'ELIGIBLE';
                           $select->status_changed_at = now();
                           $select->save();
                         }
                       }
                   }
                   break;
                }
              }
           }
        }

        // Phase II
        $choices = array(1,2,3,4);
        $applicants = Applicant::with(['selections','nectaResultDetails.results','nacteResultDetails.results'])->where('program_level_id',$request->get('award_id'))->whereHas('selections',function($query) use($request){
            $query->where('application_window_id',$request->get('application_window_id'))->where('status','ELIGIBLE');
        })->get();
        
        foreach($choices as $choice){   
            foreach ($campus_programs as $program) {
                $count = 0;
                if(isset($program->entryRequirements[0])){
                foreach($applicants as $applicant){
                  
                  foreach($applicant->selections as $selection){
                     if($selection->order == $choice && $selection->campus_program_id == $program->id){
                        if($count <= $program->entryRequirements[0]->max_capacity && $selection->status == 'ELIGIBLE' && !$selected_program[$applicant->id]){
                           $select = ApplicantProgramSelection::find($selection->id);
                           $select->status = 'APPROVING';
                           $select->status_changed_at = now();
                           $select->save();

                           $selected_program[$applicant->id] = true;

                           $count++;
                        }
                     }
                  }
                }
              }
           }
        }

        return redirect()->back()->with('message','Selection run successfully');
    }
}
