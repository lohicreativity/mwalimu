<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Models\FeeType;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Auth;

class AdmissionController extends Controller
{
    /**
     * Payments
     */
    public function payments(Request $request)
    {
    	$applicant = User::find(Auth::user()->id)->applicants()->with(['applicationWindow','selections'=>function($query){
    		  $query->where('status','SELECTED');
    	}])->where('campus_id',session('applicant_campus_id'))->first();
    	$ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query){
    		   $query->where('year','LIKE','%'.$ac_year.'%');
    	})->first();
    	$program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();
    	$program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Programme fee%');
    	})->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	if($applicant->hostel_available_status == 1){
    		$hostel_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%Hostel%');
    		})->where('study_academic_year',$study_academic_year->id)->first();
    	    $hostel_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Hostel%');
    	    })->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	}else{
    		$hostel_fee = null;
    	}
    	if($applicant->insurance_available_status == 0){
            $insurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NHIF%');
    		})->where('study_academic_year',$study_academic_year->id)->first();
    		$insurance_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%NHIF%');
    	    })->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	}else{
    		$insurance_fee = null;
    	}
    	$data = [
           'applicant'=>$applicant,
           'program_fee'=>$program_fee,
           'hostel_fee'=>$hostel_fee,
           'insurance_fee'=>$insurance_fee
    	];
    	return view('admission.payments',$data)->withTitle('Payments');
    }

        /**
     * Store appeals
     */
    public function requestPaymentControlNumber(Request $request)
    {
    	$applicant = User::find(Auth::user()->id)->applicants()->with(['country','applicationWindow','selections'=>function($query){
    		  $query->where('status','SELECTED');
    	},'feeItem.feeType'])->where('campus_id',session('applicant_campus_id'))->first();
    	$ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query){
    		   $query->where('year','LIKE','%'.$ac_year.'%');
    	})->first();
    	$program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();
    	if($applicant->country->code == 'TZ'){
             $amount = $program_fee->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $program_fee->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $program_fee->feeItem->feeType->description,
                                    $program_fee->feeItem->feeType->gfs_code,
                                    $program_fee->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $program_fee->feeItem->feeType->duration,
                                    $invoice->currency);
    	if($applicant->hostel_available_status == 1){
    		$hostel_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%Hostel%');
    		})->where('study_academic_year',$study_academic_year->id)->first();
    		if($applicant->country->code == 'TZ'){
             $amount = $program_fee->amount_in_tzs;
             $currency = 'TZS';
         }else{
             $amount = $program_fee->amount_in_usd;
             $currency = 'USD';
         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $hostel_fee->feeItem->feeType->id;
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $hostel_fee->feeItem->feeType->description,
                                    $hostel_fee->feeItem->feeType->gfs_code,
                                    $hostel_fee->feeItem->feeType->payment_option,
                                    $student->id,
                                    $student->first_name.' '.$student->surname,
                                    $student->phone,
                                    $student->email,
                                    $generated_by,
                                    $approved_by,
                                    $hostel_fee->feeItem->feeType->duration,
                                    $invoice->currency);
    	}else{
    		$hostel_fee = null;
    	}
    	if($applicant->insurance_available_status == 0){
            $insurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NHIF%');
    		})->where('study_academic_year',$study_academic_year->id)->first();

    	}else{
    		$insurance_fee = null;
    	}

         $fee_amount = FeeAmount::whereHas('feeItem',function($query){
                   return $query->where('name','LIKE','%Appeal%');
            })->with(['feeItem.feeType'])->where('study_academic_year_id',$results[0]->moduleAssignment->study_academic_year_id)->first();

         if(!$fee_amount){
            return redirect()->back()->with('error','No fee amount set for results appeal');
         }

         

        return redirect()->back()->with('message','Control numbers requested successfully');
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
