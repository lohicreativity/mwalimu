<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\PerformanceReportRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\Invoice;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Carbon\Carbon;
use Auth;

class PerformanceReportRequestController extends Controller
{
    /**
     * Display a list of transcrript requests
     */
    public function index(Request $request)
    {
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'performance_report_requests'=>$request->has('query')? PerformanceReportRequest::whereHas('student',function($query) use($request){
                   $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->with(['student.campusProgram.program'])->paginate(20) : PerformanceReportRequest::with(['student.campusProgram.program'])->where('payment_status','PAID')->latest()->paginate(20),
    	   'request'=>$request
		];
    	return view('dashboard.academic.performance-report-requests',$data)->withTitle('Performance Report Requests');
    }

    /**
     * Attend report
     */
    public function ready(Request $request)
    {
        $report = PerformanceReportRequest::find($request->get('report_id'));
		if($report->status == 'ATTENDED'){
			return redirect()->back()->with('error','Request already attended');
		}
        $report->status = 'ATTENDED';
        $report->save();

        return redirect()->to('academic/results/'.$report->student_id.'/'.$report->study_academic_year_id.'/'.$report->year_of_study.'/show-student-perfomance-report');
    }

     /**
     * Store appeals
     */
    public function store(Request $request)
    {  
    	//  $student = User::find(Auth::user()->id)->student()->with(['applicant','registrations'=>function($query) use($request){
		// 	 $request->get('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id', intval(session('active_semester_id')));
		//  }])->first();

         $student = User::find(Auth::user()->id)->student()->with(['applicant','registrations'=>function($query) use($request){
			 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id', session('active_semester_id'));
		 }])->first();
		 
		 if(count($student->registrations) == 0){
			 return redirect()->back()->with('error','You have not been registered yet.');
		 }
         
		 $pf = PerformanceReportRequest::where('student_id',$student->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->first();
         if($pf){
			if(Carbon::parse($pf->created_at)->addDays(14)->format('Y-m-d') > date('Y-m-d')){
                 return redirect()->back()->with('error','You have aleady requested for perfomance report control number');
			}				
		 }
         $perf = PerformanceReportRequest::where('student_id',$student->id)->where('year_of_study',$request->get('year_of_study'))->whereDate('created_at','=',date('Y-m-d'))->first();
         if(!$perf){   
         $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                       $query->where('name','LIKE','%Statement%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for statement of results');
         }

         $performance = new PerformanceReportRequest;
         $performance->student_id = $student->id;
         $performance->study_academic_year_id = $request->get('study_academic_year_id');
         $performance->year_of_study = $request->get('year_of_study');
         $performance->payment_status = 'PENDING';
         $performance->save();

         if($student->applicant->country->code == 'TZ'){
             $amount = $fee_amount->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $fee_amount->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $student->id;
        $invoice->payable_type = 'student';
		$invoice->applicable_id = $request->get('study_academic_year_id');
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

            return $result;

            
            //return redirect()->back()->with('message','The bill with id '.$billno.' has been posted to bill controller.', 200);
                        
        }
}
