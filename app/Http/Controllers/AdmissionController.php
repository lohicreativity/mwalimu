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
use App\Domain\Registration\Models\Student;
use App\Http\Controllers\NHIFService;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use App\Domain\Settings\Models\SpecialDate;
use app\Utils\DateMaker;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    /**
     * Payments
     */
    public function payments(Request $request)
    {
    	$applicant = User::find(Auth::user()->id)->applicants()->doesntHave('student')->where('campus_id',session('applicant_campus_id'))
        ->with([
            'applicationWindow',
            'selections' => fn($query) => $query->select('id', 'status', 'campus_program_id', 'applicant_id')->where('status', 'SELECTED'),
            'selections.campusProgram:id,program_id',
            'selections.campusProgram.program:id,name,award_id',
            'selections.campusProgram.program.award:id,name',
            ])->where('status','ADMITTED')->first();
         
        $student = Student::where('applicant_id', $applicant->id)->first();

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

        $usd_currency = Currency::where('code','USD')->first();

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
            if(str_contains($applicant->selections[0]->campusProgram->program->name, 'Education')){
                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
            }else {
                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                        ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                        ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
            }
    	}else{
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();

            $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                    ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                    ->orWhere('name','Practical Training')->orWhere('name','LIKE', '%Graduation Gown Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                    ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

            $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTE%')->where('name', 'NOT LIKE','%TCU%')
                    ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fees%')
                    ->orWhere('name','Practical Training')->orWhere('name','LIKE', '%Graduation Gown Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                    ->orWhere('name','LIKE','%Student Union%')->orWhere('name','LIKE','%Medical Examination%');
            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
    	}

        if(!$quality_assurance_fee){
            return redirect()->back()->with('error','Quality assurance fee has not been defined for '.$study_academic_year->academicYear->year);
        }


        // $other_fees_tzs = FeeAmount::whereHas('feeItem',function($query){
    	// 		$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
    	// 	})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_tzs');
        // $other_fees_usd = FeeAmount::whereHas('feeItem',function($query){
    	// 		$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
    	// 	})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_usd');


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
			if(str_contains($applicant->hostel_available_status,1)){
				$hostel_fee_amount = $hostel_fee->amount_in_tzs;
			}
		}else{
			$program_fee_amount = $program_fee->amount_in_usd*$usd_currency->factor;
			$other_fee_amount = $other_fees_usd*$usd_currency->factor;
			if(str_contains($applicant->hostel_available_status,1)){
				$hostel_fee_amount = $hostel_fee->amount_in_usd*$usd_currency->factor;
			}
		}

        $special_dates = SpecialDate::where('name','Orientation')
        ->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicant->intake->name)->where('campus_id',$applicant->campus_id)->get();

        $orientation_date = null;
        if(count($special_dates) == 0){
            foreach($special_dates as $special_date){
                if(in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    $orientation_date = $special_date->date;
                }
            }
        }

        $now = strtotime(date('Y-m-d'));
        $orientation_date_time = strtotime($orientation_date);
        $datediff = $orientation_date_time - $now;
		$datediff = round($datediff / (60 * 60 * 24));
        $datediff = $datediff > 14? true : false;

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
           'campus'=>Campus::find(session('applicant_campus_id')),
           'datediff'=>$datediff
    	];

        if ($student) {
            return redirect()->back()->with('error', 'Unable to view page');
         }else {
            if($applicant->confirmation_status == 'CANCELLED'){
                  return redirect()->to('application/basic-information')->with('error','This action cannot be performed. Your admission has been cancelled');
             }
            return view('dashboard.admission.payments',$data)->withTitle('Payments');
         }
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
            return redirect()->back()->with('error','Programme fee has not been set.');
        }

        $special_dates = SpecialDate::where('name','Orientation')
        ->where('study_academic_year_id',$study_academic_year->id)
        ->where('intake',$applicant->intake->name)->where('campus_id',$applicant->campus_id)->get();

        $orientation_date = null;
        if(count($special_dates) == 0){
            return redirect()->back()->with('error','Orientation date has not been defined.');
        }else{
            foreach($special_dates as $special_date){
                if(!in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    return redirect()->back()->with('error','Orientation date for '.$applicant->selections[0]->campusProgram->program->award->name.' has not been defined');
                }else{
                    $orientation_date = $special_date->date;
                }
            }
        }

        if(empty($applicant->phone) || empty($applicant->email)){
            return redirect()->back()->with('error','Please provide mobile phone number and email address.');
        }

        $now = strtotime(date('Y-m-d'));
        $orientation_date_time = strtotime($orientation_date);
        $datediff = $orientation_date_time - $now;
		$datediff = round($datediff / (60 * 60 * 24));
        //return date('Y-m-d').'-'.$orientation_date.'-'.$datediff;
        if($datediff > 14){
            return redirect()->back()->with('error','This action cannot be performed now.');
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
		$invoice->applicable_id = $study_academic_year->id;
		$invoice->applicable_type = 'academic_year';
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        $first_name = str_contains($applicant->first_name,"'")? str_replace("'","",$applicant->first_name) : $applicant->first_name; 
        $surname = str_contains($applicant->surname,"'")? str_replace("'","",$applicant->surname) : $applicant->surname;

        $result = $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $program_fee->feeItem->feeType->description,
                                    $program_fee->feeItem->feeType->gfs_code,
                                    $program_fee->feeItem->feeType->payment_option,
                                    $applicant->id,
                                    $first_name.' '.$surname,
                                    $applicant->phone,
                                    $email,
                                    $generated_by,
                                    $approved_by,
                                    $program_fee->feeItem->feeType->duration,
                                    $invoice->currency);
        }

		// Kama mkopo kiasi cha fee anachopata ni zaidi ya 60%
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
        $invoice->actual_amount = $amount;
        $invoice->amount = $amount;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $hostel_fee->feeItem->feeType->id;
		$invoice->applicable_id = $study_academic_year->id;
		$invoice->applicable_type = 'academic_year';
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        return $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $hostel_fee->feeItem->feeType->description,
                                    $hostel_fee->feeItem->feeType->gfs_code,
                                    $hostel_fee->feeItem->feeType->payment_option,
                                    $applicant->id,
                                    $first_name.' '.$surname,
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
    			$query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
    		})->where('study_academic_year_id',$study_academic_year->id)->first();
    	}
        
        $other_fees_tzs = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->sum('amount_in_tzs');
        $other_fees_usd = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('is_mandatory',1)->where('name','NOT LIKE','%NACTVET%')->where('name','NOT LIKE','%TCU%');
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
        $invoice->actual_amount = $other_fees;
        $invoice->amount = $other_fees;
        $invoice->currency = $currency;
        $invoice->payable_id = $applicant->id;
        $invoice->payable_type = 'applicant';
        $invoice->fee_type_id = $feeType->id;
		$invoice->applicable_id = $study_academic_year->id;
		$invoice->applicable_type = 'academic_year';
        $invoice->save();


        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = config('constants.SUBSPCODE');

        return $this->requestControlNumber($request,
                                    $invoice->reference_no,
                                    $inst_id,
                                    $invoice->amount,
                                    $feeType->description,
                                    $feeType->gfs_code,
                                    $feeType->payment_option,
                                    $applicant->id,
                                    $first_name.' '.$surname,
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

         

        return redirect()->back()->with('message','Control number requested successfully');
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