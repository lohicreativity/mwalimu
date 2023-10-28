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
            ])->where('status','ADMITTED')->latest()->first();

        if(!$applicant){
            return redirect()->back()->with('error', 'Unable to view page');
        }else{
            $student = Student::where('applicant_id', $applicant->id)->first();
        }    


    	$ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
    		   $query->where('year','LIKE','%'.$ac_year.'/%');
    	})->first();
        if(!$study_academic_year){
            return redirect()->back()->with('error','Study academic year has not been created');
        }
    	$program_fee = ProgramFee::where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$applicant->selections[0]->campus_program_id)->first();
        if(!$program_fee){
            return redirect()->back()->with('error','Programme fee has not been defined. Please contact the Admission Office.');
        }

        $usd_currency = Currency::where('code','USD')->first();

    	$program_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
    	})->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();

        $fee_paid_amount = $program_fee_invoice? GatewayPayment::where('bill_id', $program_fee_invoice->reference_no)->sum('paid_amount'): 0;
       
        $hostel_fee = null;
        $hostel_fee_amount = null;
        $hostel_fee_invoice = null;
        if($applicant->hostel_available_status == 1){
            if($applicant->campus_id == 1){
                if($applicant->hostel_status == 1){
                    $hostel_fee = FeeAmount::whereHas('feeItem',function($query){
                        $query->where('name','LIKE','%Accommodation%')->where('name','NOT LIKE','%Kijichi Hostel%')->where('campus_id',1);
                    })->where('study_academic_year_id',$study_academic_year->id)->first();
                    $hostel_fee_invoice = Invoice::whereHas('feeType',function($query){
                           $query->where('name','LIKE','%Accommodation%');
                    })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
                }elseif($applicant->hostel_status == 2){
                    $hostel_fee = FeeAmount::whereHas('feeItem',function($query){
                        $query->where('name','LIKE','%Kijichi Hostel%')->where('campus_id',1);
                    })->where('study_academic_year_id',$study_academic_year->id)->first();
                    $hostel_fee_invoice = Invoice::whereHas('feeType',function($query){
                           $query->where('name','LIKE','%Accommodation%');
                    })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
                }
            }else{
                $hostel_fee = FeeAmount::whereHas('feeItem',function($query) use($applicant){
                    $query->where('name','LIKE','%Accommodation%')->where('campus_id',$applicant->campus_id);
                })->where('study_academic_year_id',$study_academic_year->id)->first();
                $hostel_fee_invoice = Invoice::whereHas('feeType',function($query){
                       $query->where('name','LIKE','%Accommodation%');
                })->with('gatewayPayment')->where('payable_id',$applicant->id)->where('payable_type','applicant')->first();
            }


            if(!$hostel_fee){
                return redirect()->back()->with('error','Hostel fee has not been defined. Please contact the Admission Office.');
            }
    	}

        $hostel_paid_amount = $hostel_fee_invoice? GatewayPayment::where('bill_id', $hostel_fee_invoice->reference_no)->sum('paid_amount'): 0;
        
    	if(str_contains($applicant->programLevel->name,'Bachelor')){
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%TCU%');
    		})->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->first();
            if(str_contains($applicant->selections[0]->campusProgram->program->name, 'Education')){
                if(session('applicant_campus_id') == 1){
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                        ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                        })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                }else {
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                        })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                }

            }else {
                if(session('applicant_campus_id') == 1){
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                        })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
                }else{
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                        })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                }
             }

        }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){

            $medical_insurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Medical Care%');})->first();

            if(!$medical_insurance_fee){
            return redirect()->back()->with('error','Medical insurance fee has not been defined. Please contact the Admission Office.');
            }

            $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%union%');})->first();

            if(!$students_union_fee){
            return redirect()->back()->with('error','Students Union fee has not been defined. Please contact the Admission Office.');
            }

            $caution_money_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%Caution Money%');})->first();

            if(!$caution_money_fee){
            return redirect()->back()->with('error','Caution Money fee has not been defined. Please contact the Admission Office.');
            }

            $medical_examination_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Master%')->where('name','LIKE','%Medical Examination%');})->first();

            if(!$medical_examination_fee){
            return redirect()->back()->with('error','Medical Examination fee has not been defined. Please contact the Admission Office.');
            }

            $registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Registration%');})->first();

            if(!$registration_fee){
            return redirect()->back()->with('error','Registration fee has not been defined. Please contact the Admission Office.');
            }

            $identity_card_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%New ID Card%');})->first();

            if(!$identity_card_fee){
            return redirect()->back()->with('error','ID card fee for new students has not been defined. Please contact the Admission Office.');
            }

            $welfare_emergence_fund = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
            ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
            ->where('name','LIKE','%Master%')->where('name','LIKE','%Welfare%')->where('name','LIKE','%Emergence%');})->first();

            if(!$welfare_emergence_fund){
            return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined. Please contact the Admission Office.");
            }

            $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                ->where('name','LIKE','%Master%')->where('name','LIKE','%TCU%');})->first();
            if(!$quality_assurance_fee){
                return redirect()->back()->with('error','TCU quality assurance fee has not been defined. Please contact the Admission Office.');
            }

            $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card Fee%')
                    ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')->orWhere('name','LIKE','%Caution Money%')
                    ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');
    
    	}else{
    		$quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
    			$query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
    		})->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->first();

            if(session('applicant_campus_id') == 1){
                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
            }else{
                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','Student\'s Union%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                        ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                        ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                        ->orWhere('name','LIKE','Student\'s Union%');
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

            }
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

        $tuition_fee_loan = LoanAllocation::where('applicant_id',$applicant->id)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)
                                          ->where('campus_id',$applicant->campus_id)->sum('tuition_fee');


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
           'program_fee_invoice'=>$program_fee_invoice? $program_fee_invoice : null,
           'fee_paid_amount'=>$fee_paid_amount,
           'hostel_fee_invoice'=>$hostel_fee_invoice? $hostel_fee_invoice : null,
           'hostel_paid_amount'=>$hostel_paid_amount,
           'insurance_fee_invoice'=>$insurance_fee_invoice,
           'other_fee_invoice'=>$other_fee_invoice,
           'tuition_fee_loan'=>$tuition_fee_loan>0? $tuition_fee_loan : 0,
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
    	}])->where('campus_id',session('applicant_campus_id'))->latest()->first();
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
            foreach($special_dates as $key => $special_date){
                if(in_array($applicant->selections[0]->campusProgram->program->award->name, unserialize($special_date->applicable_levels))){
                    $orientation_date = $special_date->date;
                    break;
                }else{
                    if($key == (count($special_dates)-1)){
                        return redirect()->back()->with('error','Orientation date for '.$applicant->selections[0]->campusProgram->program->award->name.' has not been defined');
                    }
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

        $loan_allocation = LoanAllocation::where('applicant_id',$applicant->id)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)->sum('tuition_fee');
        $tuition_fee_loan = LoanAllocation::where('applicant_id',$applicant->id)->where('year_of_study',1)->where('study_academic_year_id',$study_academic_year->id)
                                          ->where('campus_id',$applicant->campus_id)->sum('tuition_fee');

        if($loan_allocation){
             if(str_contains($applicant->nationality,'Tanzania')){
                 $amount = $program_fee->amount_in_tzs - $tuition_fee_loan;
                 $amount_loan = round($tuition_fee_loan);
                 $currency = 'TZS';
             }else{
                 $amount = round(($program_fee->amount_in_usd - $tuition_fee_loan/$usd_currency->factor) * $usd_currency->factor);
                 $amount_loan = round($tuition_fee_loan);
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

        $first_name = str_contains($applicant->first_name,"'")? str_replace("'","",$applicant->first_name) : $applicant->first_name;
        $surname = str_contains($applicant->surname,"'")? str_replace("'","",$applicant->surname) : $applicant->surname;

        if($amount != 0.00){
            // Kama mkopo kiasi cha fee anachopata ni zaidi ya 60%
            if($amount_loan/$amount_without_loan >= 0.6){
                $applicant->tuition_payment_check = 1;
                $applicant->save();
            }else{
                //return $program_fee->feeItem;
                $programFeeInvoiceRequestedCheck = Invoice::where('payable_id', $applicant->id)->where('fee_type_id', $program_fee->feeItem->feeType->id)
                ->where('applicable_id', $study_academic_year->id)->where('payable_type', 'applicant')->where('applicable_type', 'academic_year')->first(); 

                if(!$programFeeInvoiceRequestedCheck){
                
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
            }
    
        }  

        $hostel_fee = null;
    	if($applicant->hostel_available_status == 1 && $applicant->has_postponed != 1){

            if($applicant->campus_id == 1){
                if($applicant->hostel_status == 1){
                    $hostel_fee = FeeAmount::whereHas('feeItem',function($query){
                        $query->where('name','LIKE','%Accommodation%')->where('name','NOT LIKE','%Kijichi Hostel%')->where('campus_id',1);
                    })->where('study_academic_year_id',$study_academic_year->id)->first();

                }elseif($applicant->hostel_status == 2){
                    $hostel_fee = FeeAmount::whereHas('feeItem',function($query){
                        $query->where('name','LIKE','%Kijichi Hostel%')->where('campus_id',1);
                    })->where('study_academic_year_id',$study_academic_year->id)->first();

                }
            }else{
                $hostel_fee = FeeAmount::whereHas('feeItem',function($query) use($applicant){
                    $query->where('name','LIKE','%Accommodation%')->where('campus_id',$applicant->campus_id);
                })->where('study_academic_year_id',$study_academic_year->id)->first();

            }

    		if(str_contains($applicant->nationality,'Tanzania')){
	             $amount = round($hostel_fee->amount_in_tzs);
	             $currency = 'TZS';
	         }else{
	             $amount = round($hostel_fee->amount_in_usd*$usd_currency->factor);
	             $currency = 'TZS'; //'USD';
	         }

        $hostelFeeInvoiceRequestedCheck = Invoice::where('payable_id', $applicant->id)->where('fee_type_id', $hostel_fee->feeItem->feeType->id)->where('applicable_id', $study_academic_year->id)->where('payable_type', 'applicant')->where('applicable_type', 'academic_year')->first(); 
        if(!$hostelFeeInvoiceRequestedCheck){
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
        }
    	}

        if($applicant->has_postponed != 1){

            if(str_contains($applicant->programLevel->name,'Bachelor')){
                $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                    $query->where('name','LIKE','%TCU%');
                })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->first();

                if(str_contains($applicant->selections[0]->campusProgram->program->name, 'Education')){
                    if(session('applicant_campus_id') == 1){
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
                    }else{
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Teaching Practice%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                    }

                }else {
                    if(session('applicant_campus_id') == 1){
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
                    }else{
                        $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                        $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                            $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                                ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                                ->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                                ->orWhere('name','LIKE','Student\'s Union%');
                            })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                    }
                }
            }elseif(str_contains(strtolower($applicant->selections[0]->campusProgram->program->name), 'education')){
                $medical_insurance_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%Medical Care%');})->first();
    
                if(!$medical_insurance_fee){
                return redirect()->back()->with('error','Medical insurance fee has not been defined. Please contact the Admission Office.');
                }
    
                $students_union_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Master%')->where('name','LIKE','%student%')->where('name','LIKE','%union%');})->first();
    
                if(!$students_union_fee){
                return redirect()->back()->with('error','Students Union fee has not been defined. Please contact the Admission Office.');
                }
    
                $caution_money_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Master%')->where('name','LIKE','%Caution Money%');})->first();
    
                if(!$caution_money_fee){
                return redirect()->back()->with('error','Caution Money fee has not been defined. Please contact the Admission Office.');
                }
    
                $medical_examination_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                        ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                        ->where('name','LIKE','%Master%')->where('name','LIKE','%Medical Examination%');})->first();
    
                if(!$medical_examination_fee){
                return redirect()->back()->with('error','Medical Examination fee has not been defined. Please contact the Admission Office.');
                }
    
                $registration_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%Registration%');})->first();
    
                if(!$registration_fee){
                return redirect()->back()->with('error','Registration fee has not been defined. Please contact the Admission Office.');
                }
    
                $identity_card_fee = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                    ->where('name','LIKE','%Master%')->where('name','LIKE','%New ID Card%');})->first();
    
                if(!$identity_card_fee){
                return redirect()->back()->with('error','ID card fee for new students has not been defined. Please contact the Admission Office.');
                }
    
                $welfare_emergence_fund = FeeAmount::where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                ->where('name','LIKE','%Master%')->where('name','LIKE','%Welfare%')->where('name','LIKE','%Emergence%');})->first();
    
                if(!$welfare_emergence_fund){
                return redirect()->back()->with('error',"Student's welfare emergency fund has not been defined. Please contact the Admission Office.");
                }
    
                $quality_assurance_fee = FeeAmount::select('amount_in_tzs','amount_in_usd')->where('study_academic_year_id',$study_academic_year->id)->where('campus_id',$applicant->campus_id)
                                                    ->whereHas('feeItem',function($query) use($applicant){$query->where('campus_id',$applicant->campus_id)
                                                    ->where('name','LIKE','%Master%')->where('name','LIKE','%TCU%');})->first();
                if(!$quality_assurance_fee){
                    return redirect()->back()->with('error','TCU quality assurance fee has not been defined. Please contact the Admission Office.');
                }
    
                $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                    $query->where('name','LIKE','%Master%')->where(function($query){$query->where('name','LIKE','%Registration Fee%')->orWhere('name','LIKE','%New ID Card Fee%')
                        ->orWhere('name','LIKE','%Supervision Fee%')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')->orWhere('name','LIKE','%Caution Money%')
                        ->orWhere('name','LIKE','%Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');});
                })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');
                
            }else{
                $quality_assurance_fee = FeeAmount::whereHas('feeItem',function($query){
                    $query->where('name','LIKE','%NACTVET%')->where('name','LIKE','%Quality%');
                })->where('study_academic_year_id',$study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->first();

                if(session('applicant_campus_id') == 1){
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%')->orWhere('name','LIKE','%Medical Examination%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');
                }else{
                    $other_fees_tzs = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_tzs');

                    $other_fees_usd = FeeAmount::whereHas('feeItem', function($query){
                        $query->where('is_mandatory',1)->where('name', 'NOT LIKE', '%NACTVET%')->where('name', 'NOT LIKE','%TCU%')
                            ->where('name','Caution Money')->orWhere('name','Registration Fee')->orWhere('name', 'LIKE','%New ID Card Fee%')
                            ->orWhere('name','Practical Training')->orWhere('name','LIKE','%Student\'s Welfare Emergence%')
                            ->orWhere('name','LIKE','Student\'s Union%');
                    })->where('study_academic_year_id', $study_academic_year->id)->where('campus_id', session('applicant_campus_id'))->sum('amount_in_usd');

                }
            }

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

            $otherFeeInvoiceRequestedCheck = Invoice::where('payable_id', $applicant->id)->where('payable_type', 'applicant')->where('applicable_id', $study_academic_year->id)->where('fee_type_id', $feeType->id)->where('applicable_type', 'academic_year')->first();
            if(!$otherFeeInvoiceRequestedCheck){
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

                    //   if($result){
                    //     return redirect()->back()->with('error','There is a technical problem, please contact an Admission Officer');
                    // }

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
