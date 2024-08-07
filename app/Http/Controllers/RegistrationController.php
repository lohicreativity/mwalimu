<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Application\Models\ExternalTransfer;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Registration\Models\Student;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Registration;
use App\Domain\Registration\Models\IdCardRequest;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Settings\Models\Currency;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManagerStatic as Image;
use Auth, DomPDF, File, Storage, PDF;
use Carbon\Carbon;
use App\Utils\DateMaker;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Application\Models\TCUApiErrorLog;
use App\Utils\Util;

class RegistrationController extends Controller
{
    /**
     * Create new registration
     */
    public function create(Request $request)
    {

        $student = User::find(Auth::user()->id)->student()->with(['applicant:id,nationality,intake_id,campus_id,is_transfered','applicant.intake','studentshipStatus','academicStatus','semesterRemarks','overallRemark'])->first();
        foreach($student->semesterRemarks as $rem){
            if($student->academicStatus->name == 'RETAKE'){
                if($rem->semester_id == session('active_semester_id') && $rem->remark != 'RETAKE'){
                    return redirect()->back()->with('error','You are not allowed to register for retake in this semester');
                }
            }
        }
    //   if($student->overallRemark){
          if($student->studentshipStatus->name == 'SUPP'){
              return redirect()->back()->with('error','You are not allowed to register for this semester because you have a pending supplementary case');
          }
      //}
        if($student->studentshipStatus->name == 'POSTPONED'){
            return redirect()->back()->with('error','You cannot continue with registration because you have been postponed');
        }
        
        if($student->studentshipStatus->name == 'GRADUANT'){
            return redirect()->back()->with('error','You cannot continue with registration because you are a graduant');
        }

        if($student->academicStatus->name == 'FAIL&DISCO'){
            return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
        }

        if($student->academicStatus->name == 'ABSCOND'){
            return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }

        if($student->academicStatus->name == 'INCOMPLETE'){
            return redirect()->back()->with('error','You cannot continue with registration because you have an incomplete case');
        }

    	$annual_remarks = AnnualRemark::where('student_id',$student->id)->latest()->get();
		$semester = Semester::find(session('active_semester_id'));
        
        if(count($annual_remarks) != 0){
        	$last_annual_remark = $annual_remarks[0];
        	$year_of_study = $last_annual_remark->year_of_study;
        	if($last_annual_remark->remark == 'REPEAT'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'PASS' || $last_annual_remark->remark == 'RETAKE' || $last_annual_remark->remark == 'CARRY'){
        		if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
                   $year_of_study = $last_annual_remark->year_of_study + 1;
        		}else{
                   $year_of_study = $last_annual_remark->year_of_study;
        		}
            }
        }else{
			$year_of_study = 1;
		}

        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->with('academicYear')->first();

        if($student->applicant->intake->name == 'March' && explode('/',$student->registration_number)[3] == substr(explode('/',$study_academic_year->academicYear->year)[1],2)){

            if($semester->id == 2){
              $semester_id = 1;
            }else{
              $semester_id = $semester->id;
            }
            $ac_yr_id = $study_academic_year->id + 1;
        }else{

            $semester_id = $semester->id;
            $ac_yr_id = $study_academic_year->id;
        }

        $study_academic_year = StudyAcademicYear::where('id',$ac_yr_id)->first();

        $tuition_fee_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Tuition%');})
                                      ->where(function($query) use($student,$study_academic_year){$query->where('payable_type','student')
                                                                                                        ->where('applicable_type','academic_year')
                                                                                                        ->where('applicable_id',$study_academic_year->id)
                                                                                                        ->where('payable_id',$student->id);})
                                      ->orWhere(function($query) use($student,$study_academic_year){$query->where('payable_type','applicant')
                                                                                                          ->where('applicable_type','academic_year')
                                                                                                          ->where('applicable_id',$study_academic_year->id)
                                                                                                          ->where('payable_id',$student->applicant_id);})
                                      ->first();


        if(!$tuition_fee_invoice && $year_of_study != 1 && count($annual_remarks) != 0){
            return redirect()->back()->with('error','You have not requested for tuition fee control number');
        }

        $misc_fee_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Miscellaneous%');})
                                   ->where(function($query) use($student,$study_academic_year){$query->where('payable_type','student')
                                                                                ->where('applicable_type','academic_year')
                                                                                ->where('applicable_id',$study_academic_year->id)
                                                                                ->where('payable_id',$student->id);})
                                   ->orWhere(function($query) use($student,$study_academic_year){$query->where('payable_type','applicant')
                                                                                  ->where('applicable_type','academic_year')
                                                                                  ->where('applicable_id',$study_academic_year->id)
                                                                                  ->where('payable_id',$student->applicant_id);})
                                   ->first();

        if(!$misc_fee_invoice && $year_of_study != 1 && count($annual_remarks) != 0){
            return redirect()->back()->with('error','You have not requested for other fees control number');
        }

        $misc_fee_paid = GatewayPayment::where('control_no',$misc_fee_invoice->control_no)->sum('paid_amount');
        $tuition_fee_paid = GatewayPayment::where('control_no',$tuition_fee_invoice->control_no)->sum('paid_amount');
        $tuition_fee_loan = LoanAllocation::where(function($query) use($student){$query->where('applicant_id',$student->applicant_id);})
                                          ->where('study_academic_year_id',$study_academic_year->id)
                                          ->where('campus_id',$student->applicant->campus_id)
                                          ->sum('tuition_fee');

        if($misc_fee_paid < $misc_fee_invoice->amount && Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 1')){
            return redirect()->back()->with('error','You cannot continue with registration because you have not paid other fees');
        }

		$usd_currency = Currency::where('code','USD')->first();

		if($student->applicant->is_transfered == 1 && $year_of_study == 1){
			if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
                $new_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$student->campus_program_id)->first();

                $transfer = InternalTransfer::where('student_id',$student->id)->first();
    
                $old_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',$study_academic_year->id)->where('campus_program_id',$transfer->previous_campus_program_id)->first();
    
                $extra_fee_invoice = Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Tuition%');})
                                            ->where('payable_id',$student->id)
                                            ->where('payable_type','student')
                                            ->where('applicable_type','academic_year')
                                            ->where('applicable_id',$study_academic_year->id)
                                            ->where('id','!=',$tuition_fee_invoice->id)
                                            ->first();
    
                if($extra_fee_invoice){
                    $extra_fee_paid = GatewayPayment::where('control_no',$extra_fee_invoice->control_no)->sum('paid_amount');
                    if($extra_fee_paid){
                       $tuition_fee_paid += $extra_fee_paid;
                    }
                }
    
                if(str_contains($student->applicant->nationality,'Tanzania')){
                     $fee_diff = $new_program_fee->amount_in_tzs - $old_program_fee->amount_in_tzs;
    
                }else{
                     $fee_diff = ($new_program_fee->amount_in_usd - $old_program_fee->amount_in_usd)*$usd_currency->factor;
    
                }

				if($fee_diff > 0){
                    if($tuition_fee_loan > 0){
                        if($tuition_fee_paid + $tuition_fee_loan < $tuition_fee_invoice->amount+$fee_diff){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }else{
                        if($tuition_fee_paid < $tuition_fee_invoice->amount+$fee_diff){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }	
				}else{
                    if($tuition_fee_loan > 0){
                        if($tuition_fee_paid + $tuition_fee_loan < $tuition_fee_invoice->amount){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }else{
                        if($tuition_fee_paid < $tuition_fee_invoice->amount){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }	
                }
			}
		}else{
            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
                if($tuition_fee_loan > 0){
                    if($tuition_fee_paid + $tuition_fee_loan < $tuition_fee_invoice->amount){
                        return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                    }
                }else{
                    if($tuition_fee_paid < $tuition_fee_invoice->amount){
                        return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                    }       
                }
            }else{
                if($student->academicStatus->name == 'RETAKE'){
                    if($tuition_fee_loan > 0){
                        if($tuition_fee_paid + $tuition_fee_loan < $tuition_fee_invoice->amount){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }else{
                        if($tuition_fee_paid < $tuition_fee_invoice->amount){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }
                }else{
                    if($tuition_fee_loan > 0){
                        if($tuition_fee_paid + $tuition_fee_loan < (0.6*$tuition_fee_invoice->amount)){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }else{
                        if($tuition_fee_paid < (0.6*$tuition_fee_invoice->amount)){
                            return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
                    }
                }
            }
		}

        $registration = new Registration;
        $registration->year_of_study = $year_of_study;
        $registration->student_id = $student->id;
        $registration->study_academic_year_id = $study_academic_year->id;
        $registration->semester_id = $semester_id;
        $registration->registration_date = date('Y-m-d');
        $registration->status = 'REGISTERED';
        $registration->save();

        $stud = Student::find($student->id);
        $stud->year_of_study = $year_of_study;
        $stud->save();

    	 // $program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$year_of_study)->first();

      //    if(!$program_fee){
      //       return redirect()->back()->with('error','No programme fee set for this academic year');
      //    }

      //    if($student->applicant->country->code == 'TZ'){
      //        $amount = $program_fee->amount_in_tzs;
      //        $currency = 'TZS';
      //    }else{
      //        $amount = $program_fee->amount_in_usd;
      //        $currency = 'USD';
      //    }

      //   $invoice = new Invoice;
      //   $invoice->reference_no = 'MNMA-'.time();
      //   $invoice->amount = $amount;
      //   $invoice->currency = $currency;
      //   $invoice->payable_id = $student->id;
      //   $invoice->payable_type = 'student';
      //   $invoice->fee_type_id = $program_fee->feeItem->feeType->id;
      //   $invoice->save();

      //   $generated_by = 'SP';
      //   $approved_by = 'SP';
      //   $inst_id = config('constants.SUBSPCODE');

      //   $this->requestControlNumber($request,
      //                               $invoice->reference_no,
      //                               $inst_id,
      //                               $invoice->amount,
      //                               $program_fee->feeItem->feeType->description,
      //                               $program_fee->feeItem->feeType->gfs_code,
      //                               $program_fee->feeItem->feeType->payment_option,
      //                               $student->id,
      //                               $student->first_name.' '.$student->middle_name.' '.$student->surname,
      //                               $student->phone,
      //                               $student->email,
      //                               $generated_by,
      //                               $approved_by,
      //                               $program_fee->feeItem->feeType->duration,
      //                               $invoice->currency);

        return redirect()->to('student/request-control-number')->with('message','Registration completed successfully');
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
	 * Show statistics
	 */
	 public function statistics(Request $request)
	 {
		 $staff = User::find(Auth::user()->id)->staff;
		 if(Auth::user()->hasRole('hod')){
		 $data = [
		    'active_students'=>Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('student.studentshipStatus',function($query){
				  $query->where('name','ACTIVE')->orWhere('name', 'RESUMED');
			})->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->count(),
			'postponed_students'=>Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->count(),
			'deceased_students'=>Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
			'unregistered_students'=>Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->orWhereHas('registrations',function($query){
			$query->where('status', 'UNREGISTERED')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));})->count()
		 ];
		 }elseif(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
			 $data = [
		    'active_students'=>Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','ACTIVE')->orWhere('name', 'RESUMED');
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->count(),
			'postponed_students'=>Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->count(),
			'deceased_students'=>Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
			'unregistered_students'=>Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->orWhereHas('registrations',function($query){
			$query->where('status', 'UNREGISTERED')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));})->count()
		 ];
		 }else{
			 $data = [
		    'active_students'=>Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','ACTIVE')->orWhere('name', 'RESUMED');
			})->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->where('status','REGISTERED')->count(),
			'postponed_students'=>Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->whereHas('campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})->count(),
			'deceased_students'=>Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
			'unregistered_students'=>Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereHas('campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->orWhereHas('registrations',function($query){
			$query->where('status', 'UNREGISTERED')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));})->count()
		 ];
		 }

		 return view('dashboard.registration.statistics',$data)->withTitle('Registration Statistics');
	 }

	 /**
	  * Show active students
	  */
	  public function showActiveStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		   $active_students = null;
		   if(Auth::user()->hasRole('hod')){
			  $active_students = Registration::select('id','student_id')->whereHas('student.campusProgram.program.departments',function($query) use($staff){$query->where('id',$staff->department_id);})
			  ->whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student:id,applicant_id,first_name,middle_name,surname,gender,campus_program_id,registration_number,studentship_status_id,phone,email',
                      'student.applicant:id,index_number,program_level_id,next_of_kin_id,gender,birth_date,nationality,country_id,region_id,district_id,ward_id,entry_mode,disability_status_id,address',
                      'student.applicant.nextOfKin:id,first_name,middle_name,surname,gender,phone,nationality,address,country_id,region_id,district_id,ward_id',
                      'student.applicant.nextOfKin.country:id,name',
                      'student.applicant.nextOfKin.region:id,name',
                      'student.applicant.nextOfKin.ward:id,name',
                      'student.applicant.nacteResultDetails:id,applicant_id,verified,avn',
                      'student.applicant.nectaResultDetails:id,applicant_id,exam_id,verified,index_number',
                      'student.campusProgram.program:id,code',
			          'student.applicant.disabilityStatus:id,name',
                      'student.applicant.country:id,name',
                      'student.applicant.region:id,name',
                      'student.applicant.ward:id,name'])
			  ->where('status','REGISTERED')
			  ->where('study_academic_year_id', $request->get('study_academic_year_id'))
			  ->where('semester_id',session('active_semester_id'))->latest()->get();
		   }elseif(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
			  $active_students = Registration::select('id','student_id')->whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student:id,applicant_id,first_name,middle_name,surname,gender,campus_program_id,registration_number,studentship_status_id,phone,email',
                      'student.applicant:id,index_number,program_level_id,next_of_kin_id,gender,birth_date,nationality,country_id,region_id,district_id,ward_id,entry_mode,disability_status_id,address',
                      'student.applicant.nextOfKin:id,first_name,middle_name,surname,gender,phone,nationality,address,country_id,region_id,district_id,ward_id',
                      'student.applicant.nextOfKin.country:id,name',
                      'student.applicant.nextOfKin.region:id,name',
                      'student.applicant.nextOfKin.ward:id,name',
                      'student.applicant.nacteResultDetails:id,applicant_id,verified,avn',
                      'student.applicant.nectaResultDetails:id,applicant_id,exam_id,verified,index_number',
                      'student.campusProgram.program:id,code',
			          'student.applicant.disabilityStatus:id,name',
                      'student.applicant.country:id,name',
                      'student.applicant.region:id,name',
                      'student.applicant.ward:id,name'])
			  ->where('status','REGISTERED')
			  ->where('study_academic_year_id', $request->get('study_academic_year_id'))
			  ->where('semester_id',session('active_semester_id'))->latest()->get();
			}else{
			  $active_students = Registration::select('id','student_id')->whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student:id,applicant_id,first_name,middle_name,surname,gender,campus_program_id,registration_number,studentship_status_id,phone,email',
                      'student.applicant:id,index_number,program_level_id,next_of_kin_id,gender,birth_date,nationality,country_id,region_id,district_id,ward_id,entry_mode,disability_status_id,address',
                      'student.applicant.nextOfKin:id,first_name,middle_name,surname,gender,phone,nationality,address,country_id,region_id,district_id,ward_id',
                      'student.applicant.nextOfKin.country:id,name',
                      'student.applicant.nextOfKin.region:id,name',
                      'student.applicant.nextOfKin.ward:id,name',
                      'student.applicant.nacteResultDetails:id,applicant_id,verified,avn',
                      'student.applicant.nectaResultDetails:id,applicant_id,exam_id,verified,index_number',
                      'student.campusProgram.program:id,code',
			          'student.applicant.disabilityStatus:id,name',
                      'student.applicant.country:id,name',
                      'student.applicant.region:id,name',
                      'student.applicant.ward:id,name'])
			  ->where('status','REGISTERED')
			  ->where('study_academic_year_id', $request->get('study_academic_year_id'))
			  ->where('semester_id',session('active_semester_id'))->latest()->get();
			}


		   $data = [
		    'active_students'=>$active_students,
			'semester'=>Semester::find(session('active_semester_id')),
			'awards'=>Award::all(),
			'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
			'request'=>$request
		   ];
		   return view('dashboard.registration.active-students',$data)->withTitle('Active Students');
	  }

	  /**
	  * Download active students
	  */
	  public function downloadActiveStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		   $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=REGISTERED-STUDENTS.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
		   $students = Auth::user()->hasRole('hod')? Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){$query->where('id',$staff->department_id);})
                                                                 ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE');})
                                                                 ->whereHas('student.applicant',function($query) use($request,$staff){$query->where('program_level_id',$request->get('program_level'))->where('campus_id',$staff->campus_id);})
                                                                 ->with(['student:id,first_name,middle_name,surname,gender,phone,birth_date,campus_program_id,registration_number,applicant_id,disability_status_id',
                                                                         'student.campusProgram:id,program_id',
                                                                         'student.campusProgram.program:id,code','student.disabilityStatus:id,name','student.applicant:id,entry_mode,index_number',
                                                                         'student.applicant.nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                                                         'student.applicant.nacteResultDetails'=>function($query){$query->select('id','applicant_id','registration_number','diploma_graduation_year','programme','avn')->where('verified',1);},
                                                                         'student.applicant.outResultDetails'=>function($query){$query->select('id','applicant_id')->where('verified',1);}])->where('study_academic_year_id',session('active_academic_year_id'))
                                                                 ->where('semester_id',session('active_semester_id'))->get() : 
                                                     Registration::whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE');})
                                                                 ->whereHas('student.applicant',function($query) use($request, $staff){$query->where('program_level_id',$request->get('program_level'))->where('campus_id',$staff->campus_id);})
                                                                 ->with(['student:id,first_name,middle_name,surname,gender,phone,birth_date,campus_program_id,registration_number,applicant_id,disability_status_id',
                                                                         'student.campusProgram:id,program_id',
                                                                         'student.campusProgram.program:id,code','student.disabilityStatus:id,name','student.applicant:id,entry_mode,index_number',
                                                                         'student.applicant.nectaResultDetails'=>function($query){$query->select('id','applicant_id','index_number','exam_id')->where('verified',1);},
                                                                         'student.applicant.nacteResultDetails'=>function($query){$query->select('id','applicant_id','registration_number','diploma_graduation_year','programme','avn')->where('verified',1);},
                                                                         'student.applicant.outResultDetails'=>function($query){$query->select('id','applicant_id')->where('verified',1);}])
                                                                 ->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get();
 
		   $callback = function() use ($students)
            {
                $file_handle = fopen('php://output', 'w');
                fputcsv($file_handle, ['First Name','Middlename','Surname','Sex','Date of Birth','Disability','F4 Index#','F6 Index#','Registration Number','Program','Entry Mode','Sponsorship']);
                foreach ($students as $row) {
                    $loan_status = LoanAllocation::where('index_number',$row->student->applicant->index_number)->where(function($query){$query->where('meals_and_accomodation','>',0)->orWhere('books_and_stationeries','>',0)
                                                 ->orWhere('tuition_fee','>',0)->orWhere('field_training','>',0)->orWhere('research','>',0);})->where('study_academic_year_id',session('active_academic_year_id'))->first();
                    $sponsorship = $loan_status? 'Government' : 'Private';

                    $f4indexno = $f6indexno = [];

                    foreach($row->student->applicant->nectaResultDetails as $detail){
                        if($detail->exam_id == 1){
                            $f4indexno[] = $detail->index_number;
                        }

                        if($detail->exam_id == 2){
                            $f6indexno[] = $detail->index_number;
                        }
                    }

                    $f4indexno = count($f4indexno) > 0? $f4indexno : $row->student->applicant->index_number;

                    foreach($row->student->applicant->nacteResultDetails as $detail){
                        if($f6indexno == null && str_contains(strtolower($detail->programme),'diploma')){
                            $f6indexno = $detail->avn;
                            break;
                        }
                    }

                    if(is_array($f4indexno)){
                        $f4indexno=implode(', ',$f4indexno);
                    }

                    if(is_array($f6indexno)){
                        $f6indexno=implode(', ',$f6indexno);
                    }
                    fputcsv($file_handle, [$row->student->first_name,$row->student->middle_name,$row->student->surname,$row->student->gender, DateMaker::toStandardDate($row->student->birth_date), 
                                           $row->student->disabilityStatus->name,$f4indexno,$f6indexno,$row->student->registration_number,
                                           $row->student->campusProgram->program->code,$row->student->applicant->entry_mode,$sponsorship
                                            ]);
                }
                fclose($file_handle);
            };

              return response()->stream($callback, 200, $headers);
	  }

	  /**
	  * Show postponed students
	  */
	  public function downoadPostponedStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		   $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=POSTPONED-STUDENTS.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
		   $students = Auth::user()->hasRole('hod')? Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->with(['campusProgram.program'])->get() : Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->with(['campusProgram.program'])->get();
		   $callback = function() use ($students)
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['Name','Sex','Registration Number','Program']);
                  foreach ($students as $row) {
                      fputcsv($file_handle, [$row->first_name.' '.$row->middle_name.' '.$row->surname,$row->gender,$row->registration_number,$row->campusProgram->program->name]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
	  }

	  /**
	  * Download postponed students
	  */
	  public function showPostponedStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		   $data = [
		    'postponed_students'=>Auth::user()->hasRole('hod')? Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->with(['campusProgram.program'])->get() : Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','POSTPONED');
			})->with(['campusProgram.program'])->get(),
			'semester'=>Semester::find(session('active_semester_id'))
		   ];
		   return view('dashboard.registration.postponed-students',$data)->withTitle('Postponed Students');
	  }

	  /**
	  * Show deceased students
	  */
	  public function showDeceasedStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		   $data = [
		    'deceased_students'=>Auth::user()->hasRole('hod')? Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get() : Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get(),
			'semester'=>Semester::find(session('active_semester_id'))
		   ];
		   return view('dashboard.registration.deceased-students',$data)->withTitle('Deceased Students');
	  }

	  /**
	  * Download deceased students
	  */
	  public function downloadDeceasedStudents(Request $request)
	  {
		   $staff = User::find(Auth::user()->id)->staff;
		    $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=DECEASED-STUDENTS.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
		   $students = Auth::user()->hasRole('hod')? Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get() : Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','DECEASED');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get();

			 $callback = function() use ($students)
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['Name','Sex','Registration Number','Program']);
                  foreach ($students as $row) {
                      fputcsv($file_handle, [$row->first_name.' '.$row->middle_name.' '.$row->surname,$row->gender,$row->registration_number,$row->campusProgram->program->name]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
	  }

	  /**
	 * Show unregistered students
	 */
	 public function showUnregisteredStudents(Request $request)
	 {
		 $staff = User::find(Auth::user()->id)->staff;
		 $data = [
			'unregistered_students'=>Auth::user()->hasRole('hod')? Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereHas('academicStatus',function($query){
				  $query->where('name','!=','FAIL&DISCO');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->with(['campusProgram.program','academicStatus'])->get() : Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereHas('academicStatus',function($query){
				  $query->where('name','!=','FAIL&DISCO');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->orWhereHas('registrations',function($query){
				  $query->where('status', 'UNREGISTERED')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->with(['campusProgram.program','academicStatus'])->get(),
			'semester'=>Semester::find(session('active_semester_id'))
		 ];

		 return view('dashboard.registration.unregistered-students',$data)->withTitle('Unregistered Students');
	 }

	   /**
	 * Download unregistered students
	 */
	 public function downloadUnregisteredStudents(Request $request)
	 {
		 $staff = User::find(Auth::user()->id)->staff;
		 $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=REGISTERED-STUDENTS.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];
		 $students = Auth::user()->hasRole('hod')? Student::whereHas('campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereHas('academicStatus',function($query){
				  $query->where('name','!=','FAIL&DISCO');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->with(['campusProgram.program','academicStatus'])->get() : Student::whereHas('studentshipStatus',function($query){
				  $query->where('name','!=','GRADUANT');
			})->whereHas('academicStatus',function($query){
				  $query->where('name','!=','FAIL&DISCO');
			})->whereDoesntHave('registrations',function($query){
				  $query->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'));
			})->with(['campusProgram.program','academicStatus'])->get();
			 $callback = function() use ($students)
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['Name','Sex','Registration Number','Program','Status']);
                  foreach ($students as $row) {
                      fputcsv($file_handle, [$row->first_name.' '.$row->middle_name.' '.$row->surname,$row->gender,$row->registration_number,$row->campusProgram->program->name,$row->academicStatus->name]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);

	 }

    /**
     * Print ID card
     */
    public function printIDCard(Request $request)
    {
/*         $student = Student::with('applicant','campusProgram.program','campusProgram.campus')->where('registration_number',$request->get('registration_number'))->first();

        $ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
          if($student){
              $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first();
              if(!$registration){
                  return redirect()->back()->with('error','Student has not been registered for this semester');
              }
              if($student->applicant->insurance_status == 0 && $ac_year->nhif_enabled == 1){
                  return redirect()->back()->with('error','Student does not have insurance');
              }
          } */
		  $student = null;
		$semester = Semester::where('status','ACTIVE')->first();
		if($request->has('study_academic_year_id')){
			$ac_year = StudyAcademicYear::where('id',$request->get('study_academic_year_id'))->first();

			$student = Student::select('id','registration_number','first_name','middle_name','surname','gender','phone','campus_program_id','signature','image','applicant_id','registration_year','year_of_study','created_at')
                              ->whereHas('registrations', function($query) use($request, $semester){$query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$semester->id)->where('id_print_status', 0)->where('status', 'REGISTERED');})
                              ->whereHas('campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
                              ->whereHas('applicant',function($query) use($request){$query->where('campus_id',$request->get('campus_id'));})
                              ->with('applicant:id,campus_id,intake_id','applicant.intake:id,name','campusProgram:id,code')->latest()->paginate(200);

            // $student = Student::whereHas('registrations', function($query) use($request, $semester){$query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$semester->id)->where('id_print_status', 0)->where('status', 'REGISTERED');})
            // ->whereHas('campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
            // ->whereHas('applicant',function($query) use($request){$query->where('campus_id',$request->get('campus_id'));})
            // ->with('applicant','campusProgram.program','campusProgram.campus')->latest()->paginate(200);
		}

/*         if(count($student) == 0){
          return redirect()->back()->with('error','Student has not been registered for this semester');
        } */
/*         if(count($student) > 0){
		   if($student->applicant->insurance_status == 0 && $ac_year->nhif_enabled == 1){
              return redirect()->back()->with('error','Student does not have insurance');
           }
        } */

        $data = [
            'students'=>$student? $student : [],
            'semester'=>$semester,
			'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : StudyAcademicYear::where('status', 'ACTIVE')->first(),
            'awards'=>Award::all(),
            'campuses'=>Campus::all(),
			'staff'=>User::find(Auth::user()->id)->staff,
            'compose'=>0,
            'request'=>$request
        ];
        return view('dashboard.registration.id-card',$data)->withTitle('ID Card');
    }

    public function showPrintedIDCards(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();

        $cards = Registration::select('id','student_id','id_sn_no','id_print_date','printed_by_user_id')
                               ->whereHas('student.applicant',function($query) use($staff,$request){$query->where('program_level_id',$request->get('program_level_id'))->where('campus_id',$staff->campus_id);})
                               ->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->where('id_print_status',1)
                               ->with(['student:id,first_name,middle_name,surname,gender,phone,registration_number,campus_program_id,user_id','student.campusProgram:id,code'])
                               ->orderBy('id_print_date','DESC')->get();
            

        $data = [
            'cards'=>$cards? $cards : [],
            'semester'=>$semester,
			'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : StudyAcademicYear::where('status', 'ACTIVE')->first(),
            'awards'=>Award::all(),
            'campuses'=>Campus::all(),
			'staff'=>User::find(Auth::user()->id)->staff,
            'compose'=>0,
            'request'=>$request
        ];
        return view('dashboard.registration.printed-id-cards',$data)->withTitle('Printed ID Cards');
    }

    public function composeIDCard(Request $request){
        $data = [
            'semester'=>Semester::where('status','ACTIVE')->first(),
			'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : StudyAcademicYear::where('status', 'ACTIVE')->first(),
            'awards'=>Award::all(),
            'campuses'=>Campus::all(),
			'staff'=>User::find(Auth::user()->id)->staff,
            'students'=>Student::select('id','signature','image','campus_program_id')->where('id',$request->id)->with('campusProgram:id,code')->paginate(1),
            'compose'=>1,
            'request'=>$request
        ];
        return view('dashboard.registration.id-card',$data)->withTitle('ID Card');
    }
    /**
     * Show ID Card
     */
    public function showIDCard(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $student = Student::with('campusProgram.program','campusProgram.campus', 'applicant.intake')
        ->where('registration_number',str_replace('-','/',$request->get('registration_number')))->first();
        $ac_year = StudyAcademicYear::where('status','ACTIVE')->with('academicYear')->first();

        $semester = Semester::where('status','ACTIVE')->first();
        if(!$student->image){
            return redirect()->back()->with('error','Student image is missing');
        }
        if(!$student->signature){
                return redirect()->back()->with('error','Student signature is missing');
        }
        if($student->applicant->intake->name == 'March'){
            $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id + 1)
            ->where('semester_id',$semester->id)->first();
        }else{
            $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)
            ->where('semester_id',$semester->id)->first();
        }

        if(!$registration){
             return redirect()->back()->with('error','Student has not been registered for this semester');
        }

        $id_requests = IdCardRequest::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('is_printed',0)->get();

        if(count($id_requests) == 0 && $registration->id_print_status != 0){
            return redirect()->back()->with('error','Student ID already printed');
        }

        $tuition_payment_check = null;
        $invoice = Invoice::whereHas('feeType',function($query) use($student){
            $query->where('name','LIKE','%Tuition%');
                     })->where('payable_id',$student->id)->where('payable_type','student')->whereNotNull('gateway_payment_id')->first();

        if($invoice) {
            if($invoice->gatewayPayment->ccy == 'TZS'){
                $program_fee = ProgramFee::where('study_academic_year_id',$ac_year->id)->where('campus_program_id',$student->campusProgram->id)->where('year_of_study', $student->year_of_study)->pluck('amount_in_tzs');
            }else if($invoice->gatewayPayment->ccy == 'USD'){
                $program_fee = ProgramFee::where('study_academic_year_id',$ac_year->id)->where('campus_program_id',$student->campusProgram->id)->where('year_of_study', $student->year_of_study)->pluck('amount_in_usd');
            }
            $paid_tuition_fees = GatewayPayment::where('control_no', $invoice->control_no)->sum('paid_amount');

            if($paid_tuition_fees == $program_fee[0]){
                $tuition_payment_check = true;
            }else{
                $tuition_payment_check = false;
            }
        }

        $latestRegistrationNo = Registration::where('study_academic_year_id',$ac_year->id)->whereNotNull('id_sn_no')->orderBy('id_print_date', 'desc')->first();
        $newRegistrationNo = null;
        if(!$latestRegistrationNo){
            $registration->id_sn_no = 'SN:'.$ac_year->academicYear->year.'-000001';
            $newRegistrationNo = $registration->id_sn_no;
        }else{
            $newRegNo = explode('-',$latestRegistrationNo->id_sn_no);
            $newRegistrationNo = sprintf("%06d",$newRegNo[1]+1);
            $registration->id_sn_no = 'SN:'.$ac_year->academicYear->year.'-'.$newRegistrationNo;
            $newRegistrationNo = $registration->id_sn_no;
        }
        $registration->id_print_date = now();
        $registration->printed_by_user_id = $staff->id;
        $registration->id_print_status = 1; $newRegistrationNo;
        $registration->save();

        IdCardRequest::where('study_academic_year_id',$ac_year->id)->where('student_id',$student->id)->where('is_printed',0)->update(['is_printed'=>1]);



        $data = [
            'student'=>$student,
            'semester'=>$semester,
            'study_academic_year'=>$ac_year,
            'registration_no' => $newRegistrationNo,
            'tuition_payment_check' => $tuition_payment_check
        ];

         return view('dashboard.registration.reports.id-card',$data);
         $pdf = PDF::loadView('dashboard.registration.reports.id-card',$data,[],[
               'format'=>'A7',
               'mode' => 'utf-8',
               'allow_charset_conversion' => true,
               'margin_top'=>0,
               'margin_bottom'=>0,
               'margin_left'=>0,
               'margin_right'=>0,
               'orientation'=>'L',
               'display_mode'=>'fullpage',
               'format'=>[500,400]
        ]);
        return  $pdf->stream();
        //  return view('dashboard.registration.reports.id-card',$data);
    }

    /**
     * Crop student image
     */
    public function cropStudentImage(Request $request)
    {
          $y1=$request->get('top');
          $x1=$request->get('left');
          $w=$request->get('right');
          $h=$request->get('bottom');

          $image=public_path().'/uploads/'.$request->get('image');
          $image1 = public_path().'/avatars/'.$request->get('image');

          $type = explode('.', $image)[1];

          list( $width,$height ) = getimagesize( $image );
          $newwidth = 320;
          $newheight = 240;

          switch($type){
            case 'bmp': $img = imagecreatefromwbmp($image); break;
            case 'gif': $img = imagecreatefromgif($image); break;
            case 'jpeg': $img = imagecreatefromjpeg($image); break;
            case 'png': $img = imagecreatefrompng($image); break;
            default : return "Unsupported picture type!";
          }

          $thumb = imagecreatetruecolor( $newwidth, $newheight );
          $source = $img; //imagecreatefromjpeg($image);

          imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
          // imagejpeg($thumb,$image,100);
          switch($type){
            case 'bmp': imagewbmp($thumb,$image); break;
            case 'gif': imagegif($thumb,$image); break;
            case 'jpeg': imagejpeg($thumb,$image,100); break;
            case 'png': imagepng($thumb,$image); break;
          }

          switch($type){
            case 'bmp': $img = imagecreatefromwbmp($image); break;
            case 'gif': $img = imagecreatefromgif($image); break;
            case 'jpeg': $img = imagecreatefromjpeg($image); break;
            case 'png': $img = imagecreatefrompng($image); break;
            default : return "Unsupported picture type!";
          }

          $im = $img; //imagecreatefromjpeg($image);
          $dest = imagecreatetruecolor($w,$h);

          imagecopyresampled($dest,$im,0,0,$x1,$y1,$w,$h,$w,$h);

          switch($type){
            case 'bmp': imagewbmp($dest,$image1); break;
            case 'gif': imagegif($dest,$image1); break;
            case 'jpeg': imagejpeg($dest,$image1,100); break;
            case 'png': imagepng($dest,$image1); break;
          }
          //imagejpeg($dest,$image, 100);

          return redirect()->back()->with('message','Image cropped successfully');
    }

    /**
     * Print ID Card Bulk
     */
    public function printIDCardBulk(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
        if(!$study_academic_year){
        	return redirect()->back()->with('error','No active academic year');
        }
        $semester = Semester::where('status','ACTIVE')->first();
        $data = [
            'campus_programs'=>CampusProgram::with('program')->where('campus_id',$staff->campus_id)->get(),
            'students'=>Registration::with(['student.campusProgram.campus','student.campusProgram.program'])->whereHas('student',function($query) use($request){
                    $query->where('campus_program_id',$request->get('campus_program_id'));
                })->where('study_academic_year_id',$study_academic_year->id)->get()
        ];
        return view('dashboard.registration.id-card-bulk',$data)->withTitle('ID Card Bulk');

    }

    /**
     * Show ID Card Bulk
     */
    public function showIDCardBulk(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $data = [
            'students'=>Registration::with(['student.campusProgram.campus','student.campusProgram.program'])->whereHas('student',function($query) use($request){
                    $query->where('campus_program_id',$request->get('campus_program_id'));
                })->where('study_academic_year_id',$study_academic_year->id)->take(5)->get(),
            'semester'=>$semester,
            'study_academic_year'=>$study_academic_year
        ];
        // return view('dashboard.registration.print-id-card-bulk',$data)->withTitle('Print ID Card Bulk');
        if(count($data['students']) == 0){
            return redirect()->back()->with('error','No students registered for this programme');
        }
        $pdf = DomPDF::loadView('dashboard.registration.print-id-card-bulk',$data)->setPaper('a7','landscape');
        return  $pdf->stream();
    }

    public function getTransferVerificationStatus(Request $request){
        $staff = User::find(Auth::user()->id)->staff;
        $study_ac_yr = StudyAcademicYear::select('id')->where('status','ACTIVE')->first();
        $tcu_username = $tcu_token = null;
        if($staff->campus_id == 1){
            $tcu_username = config('constants.TCU_USERNAME_KIVUKONI');
            $tcu_token = config('constants.TCU_TOKEN_KIVUKONI');
  
        }elseif($staff->campus_id == 2){
            $tcu_username = config('constants.TCU_USERNAME_KARUME');
            $tcu_token = config('constants.TCU_TOKEN_KARUME');
  
        }

        $url = $request->get('transfer_type') == 'internal'? 'http://api.tcu.go.tz/applicants/getInternalTransferStatus' : 'http://api.tcu.go.tz/applicants/getInterInstitutionalTransferStatus';
          $campus_program = CampusProgram::find($request->get('campus_program_id'));
          $xml_request = '<?xml version="1.0" encoding="UTF-8"?>
                          <Request>
                          <UsernameToken>
                          <Username>'.$tcu_username.'</Username>
                          <SessionToken>'.$tcu_token.'</SessionToken>
                          </UsernameToken>
                          <RequestParameters>
                          <ProgrammeCode>'.$campus_program->regulator_code.'</ProgrammeCode>
                          </RequestParameters>
                          </Request>
                          ';
  
            $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
            $json = json_encode($xml_response);
            $array = json_decode($json,TRUE);
    
            foreach($array['Response']['ResponseParameters']['Applicant'] as $data){
                $student = Student::select('id','applicant_id')
                                  ->whereHas('applicant',function($query) use($data,$staff,$request){$query
                                                        ->where('index_number',$data['f4indexno'])
                                                        ->where('campus_id',$staff->campus_id)
                                                        ->where('program_level_id',$request->get('program_level_id'));})
                                  ->latest()->first();
                if($student){
                    $transfer = null;
                    if($request->get('transfer_type') == 'internal'){
                        $transfer = InternalTransfer::where('student_id',$student->id)
                        ->where('status','SUBMITTED')
                        ->first();
                    }else{
                        $transfer = ExternalTransfer::where('applicant_id',$student->applicant_id)
                        ->where('status','SUBMITTED')
                        ->first();
                    }

                    if($transfer){
                        if($data['VerificationStatusCode'] == 231)
                        $transfer->status = 'APPROVED';
                        elseif($data['VerificationStatusCode'] == 232){
                            $transfer->status = 'DISAPPROVED';
                        }else{
                        $error_log = new TCUApiErrorLog;
                        $error_log->student_id = $student->id;
                        $error_log->entry_type = $request->get('transfer_type') == 'internal'? 'Internal Tranfer Status' : 'External Tranfer Status';
                        $error_log->window_id = $study_ac_yr->id;
                        $error_log->program_level_id = 4;
                        $error_log->error_code = $array['Response']['ResponseParameters']['StatusCode'];
                        $error_log->error_desc = $array['Response']['ResponseParameters']['StatusDescription'];
                        $error_log->save();
    
                        }
                        $transfer->save();
                    }
                }
                return 1;
            }
      }

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
}
