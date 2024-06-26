<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\TranscriptRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Graduant;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Auth;

class TranscriptRequestController extends Controller
{
    /**
     * Display a list of transcrript requests
     */
    public function index(Request $request)
    {
    	$data = [
           'transcript_requests'=>TranscriptRequest::where('payment_status','PAID')->where('status', null)->latest()->paginate(20)
    	];
    	return view('dashboard.academic.transcript-requests',$data)->withTitle('Transcript Requests');
    }

     /**
     * Store appeals
     */
    public function store(Request $request)
    {
    	 $student = User::find(Auth::user()->id)->student()->with('applicant')->first();

    	 if(Graduant::where('student_id',$student->id)->where('status','GRADUATING')->count() == 0){
    	 	return redirect()->back()->with('error','You are not in the graduants list, please check with the Examination Office');
    	 }

    	 if(Clearance::where('student_id',$student->id)->where('library_status',1)->where('hostel_status',1)->where('finance_status',1)->where('hod_status',1)->count() == 0){
    	 	return redirect()->back()->with('error','You have not finished clearance');
    	 }
         

         $tranx = TranscriptRequest::where('student_id',$student->id)->whereDate('created_at','=',date('Y-m-d'))->orWhere('status', null)->first();
         if(!$tranx){
          
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();

        if($student->applicant->intake_id == 2 && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){
            $ac_yr_id = $study_academic_year->id + 1;
        }else{
            $ac_yr_id = $study_academic_year->id;
        }
    
        $study_academic_year = StudyAcademicYear::with('academicYear')->where('id',$ac_yr_id)->first(); 

         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Transcript%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for transcript');
         }

          $transcript = new TranscriptRequest;
          $transcript->student_id = $student->id;
          $transcript->payment_status = 'PENDING';
          $transcript->save();

         if($student->applicant->country->code == 'TZ'){
             $amount = $fee_amount->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $fee_amount->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
		$invoice->actual_amount = $amount;
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
		$invoice->applicable_id = $study_academic_year->id;
        $invoice->applicable_type = 'academic_year';
        $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
        $invoice->save();

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name; 
        $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;

        $number_filter = preg_replace('/[^0-9]/','',$student->email);
        $payer_email = empty($number_filter)? $student->email : 'admission@mnma.ac.tz';
        $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_amount->feeItem->feeType->description,
                                    $fee_amount->feeItem->feeType->gfs_code,
                                    $fee_amount->feeItem->feeType->payment_option,
                                    $student->id,
                                    $first_name.' '.$surname,
                                    $student->phone,
                                    $payer_email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_amount->feeItem->feeType->duration,
                                    $invoice->currency);
									
		return redirect()->to('student/request-control-number')->with('message','Transcript requested successfully');
        }

        return redirect()->to('student/request-control-number')->with('message','You have already requested a transcript');
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

            return $result;

            
       // return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
                        
        }
		
	public function issueTranscript($student_id){
		
		TranscriptRequest::where('student_id',$student_id)->latest()->update(['status'=>'ISSUED']);
		
		return redirect()->to('academic/transcript-requests')->with('message','Transcript issued successfully');
	}
}
