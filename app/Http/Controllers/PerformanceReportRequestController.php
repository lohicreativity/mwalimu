<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\PerformanceReportRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\FeeAmount;
use App\Models\User;
use Auth;

class PerformanceReportRequestController extends Controller
{
    /**
     * Display a list of transcrript requests
     */
    public function index(Request $request)
    {
    	$data = [
           'performance_report_requests'=>PerformanceReportRequest::latest()->paginate(20)
    	];
    	return view('dashboard.academic.performance-report-requests',$data)->withTitle('Performance Report Requests');
    }

     /**
     * Store appeals
     */
    public function store(Request $request)
    {
    	 $student = User::find(Auth::user()->id)->student()->with('applicant')->first();
         
         $performance = new PerformanceReportRequest;
         $performance->student_id = $student->id;
         $performance->study_academic_year_id = $request->get('study_academic_year_id');
         $performance->year_of_study = $request->get('year_of_study');
         $performance->payment_status = 'PENDING';
         $performance->save();
          
         $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Performance Report%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for results appeal');
         }

         if($student->applicant->country->code == 'TZ'){
             $amount = $count*$fee_amount->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $count*$fee_amount->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.$student->registration_number.'-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
        $invoice->fee_type_id = $fee_amount->feeItem->feeType->id;
        $invoice->save();

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $this->requestControlNumber($request,
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

        return redirect()->to('student/request-control-number')->with('message','Performance report requested successfully');
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
}
