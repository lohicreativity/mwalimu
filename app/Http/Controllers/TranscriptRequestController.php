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
           'transcript_requests'=>TranscriptRequest::where('payment_status','PAID')->latest()->paginate(20)
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
    	 	return redirect()->back()->with('error','You cannot request for transcript because you are not in the graduants list');
    	 }

    	 if(Clearance::where('student_id',$student->id)->where('library_status',1)->where('hostel_status',1)->where('finance_status',1)->where('hod_status',1)->count() == 0){
    	 	return redirect()->back()->with('error','You have not finished clearance');
    	 }
         

         $tranx = TranscriptRequest::where('student_id',$student->id)->whereDate('created_at','=',date('Y-m-d'))->first();
         if(!$tranx){
          
         $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Transcript%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for transcript request');
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

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $fee_amount->feeItem->feeType->description,
                                    $fee_amount->feeItem->feeType->gfs_code,
                                    $fee_amount->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->middle_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $fee_amount->feeItem->feeType->duration,
                                    $invoice->currency);
        }

        return redirect()->to('student/request-control-number')->with('message','Transcript requested successfully');
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
		
	public function issueTranscript(Request $request){
		return $request;
	}
}
