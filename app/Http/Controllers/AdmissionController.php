<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Application\Models\Applicant;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Models\Currency;
use App\Http\Controllers\NHIFService;
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
    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
    		   $query->where('year','LIKE','%'.$ac_year.'/%');
    	})->first();
        if(!$study_academic_year){
            return redirect()->back()->with('error','Study academic year has not been created');
        }
    	$program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();
        if(!$program_fee){
            return redirect()->back()->with('error','Programme fee has not been defined for '.$study_academic_year->academicYear->year);
        }
    	$program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
    	})->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	if($applicant->hostel_available_status == 1){
    		$hostel_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%Hostel%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	    $hostel_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Hostel%');
    	    })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	}else{
    		$hostel_fee = null;
			$hostel_fee_amount = null;
    		$hostel_fee_invoice = null;
    	}


    	if(str_contains($applicant->programLevel->name,'Bachelor')){
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	}else{
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NACTE%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	}

        if(!$quality_assurance_fee){
            return redirect()->back()->with('error','Quality assurance fee has not been defined for '.$study_academic_year->academicYear->year);
        }
        
        $other_fees_tzs = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_tzs');
        $other_fees_usd = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_usd');

        $other_fees_tzs = $other_fees_tzs + $quality_assurance_fee->amount_in_tzs;
        $other_fees_usd = $other_fees_usd + $quality_assurance_fee->amount_in_usd;

        $other_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Miscellaneous%');
    	    })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();

        $loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)->first();


    	if($applicant->insurance_available_status == 0){
            $insurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NHIF%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    		$insurance_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%NHIF%');
    	    })->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
    	}else{
    		$insurance_fee = null;
    		$insurance_fee_invoice = null;
    	}

        if($study_academic_year->nhif_enabled === 0){
            $insurance_fee = null;
            $insurance_fee_invoice = null;
        }
		if(str_contains($applicant->nationality,'Tanzania')){
			$program_fee_amount = $program_fee->amount_in_tzs;
			$other_fee_amount = $other_fees_tzs;
			$hostel_fee_amount = $hostel_fee->amount_in_tzs;
		}else{
			$program_fee_amount = $program_fee->amount_in_usd*$usd_currency->factor;
			$other_fee_amount = $other_fees_usd*$usd_currency->factor;
			$hostel_fee_amount = $hostel_fee->amount_in_usd*$usd_currency->factor;
		}
    	$data = [
           'applicant'=>$applicant,
           'program_fee'=>$program_fee,
		   'program_fee_amount'=>$program_fee_amount,
		   'other_fee_amount'=>$other_fee_amount,
		   'hostel_fee_amount'=>$hostel_fee_amount,
           'hostel_fee'=>$hostel_fee,
           'insurance_fee'=>$insurance_fee,
           'other_fees_tzs'=>$other_fees_tzs,
           'other_fees_usd'=>$other_fees_usd,
           'program_fee_invoice'=>$program_fee_invoice,
           'hostel_fee_invoice'=>$hostel_fee_invoice,
           'insurance_fee_invoice'=>$insurance_fee_invoice,
           'other_fee_invoice'=>$other_fee_invoice,
           'loan_allocation'=>$loan_allocation,
           'usd_currency'=>Currency::where('code','USD')->first(),
           'campus'=>Campus::find(session('applicant_campus_id'))
    	];
    	return view('dashboard.admission.payments',$data)->withTitle('Payments');
    }

        /**
     * Store appeals
     */
    public function requestPaymentControlNumber(Request $request)
    {
    	$applicant = User::find(Auth::user()->id)->applicants()->with(['programLevel','country','applicationWindow','selections'=>function($query){
    		  $query->where('status','SELECTED');
    	}])->where('campus_id',session('applicant_campus_id'))->first();
    	$email = $applicant->email? $applicant->email : 'admission@mnma.ac.tz';

    	$ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
    		   $query->where('year','LIKE','%'.$ac_year.'/%');
    	})->first();
        $usd_currency = Currency::where('code','USD')->first();
    	$program_fee = ProgramFee::with('feeItem.feeType')->where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();

        if(!$program_fee){
            return redirect()->back()->with('error','Programme fee has not been set');
        }


        $loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)->first();
        if($loan_allocation){
             if(str_contains($applicant->nationality,'Tanzania')){
                 $amount = $program_fee->amount_in_tzs - $loan_allocation->tuition_fee;
                 $amount_loan = round($loan_allocation->tuition_fee);
                 $currency = 'TZS';
             }else{
                 $amount = round(($program_fee->amount_in_usd - $loan_allocation->tuition_fee/$usd_currency->factor) * $usd_currency->factor);
                 $amount_loan = round($loan_allocation->tuition_fee);
                 $currency = 'TZS'; //'USD';
             }
        }else{
             if(str_contains($applicant->nationality,'Tanzania')){
                 $amount = round($program_fee->amount_in_tzs);
                 $amount_loan = 0.00;
                 $currency = 'TZS';
             }else{
                 $amount = round($program_fee->amount_in_usd*$usd_currency->factor);
                 $amount_loan = 0.00;
                 $currency = 'TZS'; //'USD';
             }
        }

             if(str_contains($applicant->nationality,'Tanzania')){
                 $amount_without_loan = round($program_fee->amount_in_tzs);
             }else{
                 $amount_without_loan = round($program_fee->amount_in_usd*$usd_currency->factor);
             }
    	

        if($applicant->has_postponed == 1){
            $amount = 100000;
            $currency = 'TZS';
        }
        
        if($amount != 0.00){
        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-TF-'.time();
        $invoice->actual_amount = $amount_without_loan;
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
		$invoice->applicable_id = $applicant->applicationWindow->id;
		$invoice->applicable_type = 'application_window';
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
                                    $applicant->id,
                                    $applicant->first_name.' '.$applicant->surname,
                                    $applicant->phone,
                                    $email,
                                    $generated_by,
                                    $approved_by,
                                    $program_fee->feeItem->feeType->duration,
                                    $invoice->currency);
        }

        if($amount_loan/$amount_without_loan >= 0.6){
            $applicant->tuition_payment_check = 1;
            $applicant->save();
        }
    	if($applicant->hostel_available_status == 1 && $applicant->has_postponed != 1){
    		$hostel_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%Hostel%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    		if(str_contains($applicant->nationality,'Tanzania')){
	             $amount = round($program_fee->amount_in_tzs);
	             $currency = 'TZS';
	         }else{
	             $amount = round($program_fee->amount_in_usd*$usd_currency->factor);
	             $currency = 'TZS'; //'USD';
	         }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.$hostel_fee->feeItem->feeType->code.'-'.time();
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $hostel_fee->feeItem->feeType->id;
		$invoice->applicable_id = $applicant->applicationWindow->id;
		$invoice->applicable_type = 'application_window';
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
                                    $applicant->id,
                                    $applicant->first_name.' '.$applicant->surname,
                                    $applicant->phone,
                                    $email,
                                    $generated_by,
                                    $approved_by,
                                    $hostel_fee->feeItem->feeType->duration,
                                    $invoice->currency);
    	}else{
    		$hostel_fee = null;
    	}
        
        if($applicant->has_postponed != 1){
    	if(str_contains($applicant->programLevel->name,'Bachelor')){
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	}else{
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NACTE%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	}
        
        $other_fees_tzs = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_tzs');
        $other_fees_usd = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTE%')->where('name','NOT LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_usd');

        $other_fees_tzs = $other_fees_tzs + $quality_assurance_fee->amount_in_tzs;
        $other_fees_usd = $other_fees_usd + $quality_assurance_fee->amount_in_usd;
        if(str_contains($applicant->nationality,'Tanzania')){
        	$other_fees = round($other_fees_tzs);
        	$currency = 'TZS';
        }else{
        	$other_fees = round($other_fees_usd*$usd_currency->factor);
        	$currency = 'TZS';//'USD';
        }

        $feeType = FeeType::where('name','LIKE','%Miscellaneous%')->first();

        if(!$feeType){
            return redirect()->back()->with('error','Miscellaneous fee type has not been set');
        }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-MSC'.time();
        $invoice->amount = $other_fees;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $feeType->id;
		$invoice->applicable_id = $applicant->applicationWindow->id;
		$invoice->applicable_type = 'application_window';
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $feeType->description,
                                    $feeType->gfs_code,
                                    $feeType->payment_option,
                                    $applicant->id,
                                    $applicant->first_name.' '.$applicant->surname,
                                    $applicant->phone,
                                    $email,
                                    $generated_by,
                                    $approved_by,
                                    $feeType->duration,
                                    $invoice->currency);
        }

    	if($applicant->insurance_available_status == 0){
            $insurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NHIF%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();

    	}else{
    		$insurance_fee = null;
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

        /**
         * Submit card applications
         */
        public function submitCardApplications(Request $request)
        {
        	$applicants = Applicant::with(['insurances','selections'=>function($query){
    		  $query->where('status','SELECTED');
    	    }])->get();
    	    $ac_year = StudyAcademicYear::where('status','ACTIVE')->first()->academicYear->year;
        	return NHIFService::submitCardApplications($ac_year,$applicants);
        }
}
