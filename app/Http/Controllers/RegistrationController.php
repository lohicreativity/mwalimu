<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Registration\Models\Student;
use App\Domain\Settings\Models\Campus;
use App\Domain\Registration\Models\Registration;
use App\Domain\Registration\Models\IdCardRequest;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Settings\Models\Currency;
use App\Domain\Settings\Models\Country;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\Ward;
use App\Domain\Settings\Models\DisabilityStatus;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManagerStatic as Image;
use Auth, PDF, DomPDF, File, Storage;

class RegistrationController extends Controller
{
    /**
     * Create new registration
     */
    public function create(Request $request)
    {

    	$student = User::find(Auth::user()->id)->student()->with(['applicant','studentshipStatus','academicStatus','semesterRemarks','overallRemark'])->first();
      foreach($student->semesterRemarks as $rem){
          if($student->academicStatus->name == 'RETAKE'){
              if($rem->semester_id == session('active_semester_id') && $rem->remark != 'RETAKE'){
                      return redirect()->back()->with('error','You are not allowed to register for retake in this semester');
              }
          }
       }
      if($student->overallRemark){
          if($student->overallRemark->remark == 'SUPP'){
              return redirect()->back()->with('error','You are not allowed to register for this semester');
          }
      }
      if($student->studentshipStatus->name == 'POSTPONED'){
          return redirect()->back()->with('error','You cannot continue with registration because you have been postponed');
      }
      if($student->studentshipStatus->name == 'GRADUANT'){
          return redirect()->back()->with('error','You cannot continue with registration because you have already graduated');
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
        $semester_remarks = SemesterRemark::with('semester')->where('student_id',$student->id)->latest()->get();
        $can_register = true;
        if(count($annual_remarks) != 0){
        	$last_annual_remark = $annual_remarks[0];
        	$year_of_study = $last_annual_remark->year_of_study;
        	if($last_annual_remark->remark == 'RETAKE'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'CARRY'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'REPEAT'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'SUPP'){
                $year_of_study = $last_annual_remark->year_of_study;
        	}elseif($last_annual_remark->remark == 'PASS'){
        		if(str_contains($semester_remarks[0]->semester->name,'2')){
                   $year_of_study = $last_annual_remark->year_of_study + 1;
        		}else{
                   $year_of_study = $last_annual_remark->year_of_study;
        		}
        	}elseif($last_annual_remark->remark == 'FAIL&DISCO'){
            $can_register = false;
            return redirect()->back()->with('error','You cannot continue with registration because you have been discontinued');
          }elseif($last_annual_remark->remark == 'INCOMPLETE'){
            $can_register = false;
            return redirect()->back()->with('error','You cannot continue with registration because you have incomplete results');
          }
        }elseif(count($semester_remarks) == 1){
        	$year_of_study = 1;
        }else{
			$year_of_study = 1;
		}

        $tuition_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
        })->where(function($query) use($student){
			$query->where('payable_type','student')->where('applicable_type','academic_year')->where('applicable_id',session('active_academic_year_id'))->where('payable_id',$student->id);
		})->orWhere(function($query) use($student){
			$query->where('payable_type','applicant')->where('applicable_type','academic_year')->where('applicable_id',session('active_academic_year_id'))->where('payable_id',$student->applicant_id);
		})->first();


        if(!$tuition_fee_invoice && $year_of_study != 1 && count($annual_remarks) != 0){
            return redirect()->back()->with('error','You have not requested for tuition fee control number');
        }

        $misc_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Miscellaneous%');
        })->where(function($query) use($student){
			$query->where('payable_type','student')->where('applicable_type','academic_year')->where('applicable_id',session('active_academic_year_id'))->where('payable_id',$student->id);
		})->orWhere(function($query) use($student){
			$query->where('payable_type','applicant')->where('applicable_type','academic_year')->where('applicable_id',session('active_academic_year_id'))->where('payable_id',$student->applicant_id);
		})->first();

        if(!$misc_fee_invoice && $year_of_study != 1 && count($annual_remarks) != 0){
            return redirect()->back()->with('error','You have not requested for other fees control number');
        }

        $tuition_fee_paid = GatewayPayment::where('control_no',$tuition_fee_invoice->control_no)->sum('paid_amount');


        $misc_fee_paid = GatewayPayment::where('control_no',$misc_fee_invoice->control_no)->sum('paid_amount');
		
		$semester = Semester::find(session('active_semester_id'));
		
		$usd_currency = Currency::where('code','USD')->first();
         
		if($student->applicant->is_transfered == 1){
			if($year_of_study == 1 && str_contains($semester->name,'2')){
				$new_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',session('active_academic_year_id'))->where('campus_program_id',$student->campus_program_id)->first();
				
				$transfer = InternalTransfer::where('student_id',$student->id)->first();
				
				$old_program_fee = ProgramFee::with(['feeItem.feeType'])->where('study_academic_year_id',session('active_academic_year_id'))->where('campus_program_id',$transfer->previous_campus_program_id)->first();
				
				$extra_fee_invoice = Invoice::whereHas('feeType',function($query){
                   $query->where('name','LIKE','%Tuition%');
                })->where('payable_id',$student->id)->where('payable_type','student')->where('applicable_type','academic_year')->where('applicable_id',session('active_academic_year_id'))->where('id','!=',$tuition_fee_invoice->id)->first();
				
				if($extra_fee_invoice){
					$extra_fee_paid = GatewayPayment::where('control_no',$extra_fee_invoice->control_no)->sum('paid_amount');
					if($extra_fee_paid){
					   $tuition_fee_paid += $extra_fee_paid;
					}
				}
				
				if(str_contains($student->applicant->nationality,'Tanzania')){
				     $fee_diff = $new_program_fee->amount_in_tzs - $old_program_fee->amount_in_tzs;
					 $fee_amount = $new_program_fee->amount_in_tzs;
				}else{
					 $fee_diff = ($new_program_fee->amount_in_usd - $old_program_fee->amount_in_usd)*$usd_currency->factor;
					 $fee_amount = $new_program_fee->amount_in_usd*$usd_currency->factor;
				}
				
				if(str_contains($student->applicant->nationality,'Tanzania')){
					$new_fee_amount = $new_program_fee->amount_in_tzs;
				}else{
					$new_fee_amount = $new_program_fee->amount_in_usd*$usd_currency->factor;
				}

				if($fee_diff > 0){
					if(str_contains($semester->name,1)){
						if($student->academicStatus->name == 'RETAKE'){
                             if($tuition_fee_paid < (1*($tuition_fee_invoice->amount + $fee_diff))){
	                           return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                        }
						}else{
                            if($tuition_fee_paid < (0.6*($tuition_fee_invoice->amount + $fee_diff))){
	                           return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                        }
						}
					    
					}else{
						if($tuition_fee_paid < (1.0*($tuition_fee_invoice->amount+$fee_diff))){
                           return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                        }
					}
				}elseif($fee_diff < 0){
					if(str_contains($semester->name,1)){
					   if($student->academicStatus->name == 'RETAKE'){
						   if($tuition_fee_paid < (1*($tuition_fee_invoice->amount+$fee_diff))){
	                          return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                       }
                       }else{
                       	   if($tuition_fee_paid < (0.6*($tuition_fee_invoice->amount+$fee_diff))){
	                          return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                       }
                       }
					}else{
					   if($tuition_fee_paid < (1.0*($tuition_fee_invoice->amount+$fee_diff))){
                          return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
                       }
					}
				}
				
			}else{
				if($student->academicStatus->name == 'RETAKE'){
					if($tuition_fee_paid < (1*$tuition_fee_invoice->amount)){
	                   return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                }
                }else{
                	if($tuition_fee_paid < (0.6*$tuition_fee_invoice->amount)){
	                   return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	                }
                }
			}
		}else{
			if($student->academicStatus->name == 'RETAKE'){
	           if($tuition_fee_paid < (1*$tuition_fee_invoice->amount)){
	              return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	           }
            }else{
            	if($tuition_fee_paid < (0.6*$tuition_fee_invoice->amount)){
	              return redirect()->back()->with('error','You cannot continue with registration because you have not paid sufficient tuition fee');
	           }
            }
		}

        if($misc_fee_paid < $misc_fee_invoice->amount && str_contains($semester->name,'1')){
            return redirect()->back()->with('error','You cannot continue with registration because you have not paid other fees');
        }

        $registration = new Registration;
        $registration->year_of_study = $year_of_study;
        $registration->student_id = $student->id;
        $registration->study_academic_year_id = session('active_academic_year_id');
        $registration->semester_id = session('active_semester_id');
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
			  $active_students = Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){$query->where('id',$staff->department_id);})
			  ->whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student.applicant.nextOfKin','student.applicant.nextOfKin.country','student.applicant.nextOfKin.region','student.applicant.nextOfKin.ward','student.applicant.nacteResultDetails','student.applicant.nectaResultDetails','student.campusProgram.program',
			          'student.applicant.disabilityStatus','student.applicant.country','student.applicant.region','student.applicant.ward'])
			  ->where('status','REGISTERED')
			  ->where('study_academic_year_id', $request->get('study_academic_year_id'))
			  ->where('semester_id',session('active_semester_id'))->latest()->get();
		   }elseif(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
			  $active_students = Registration::whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student.applicant.nextOfKin','student.applicant.nextOfKin.country','student.applicant.nextOfKin.region','student.applicant.nextOfKin.ward','student.applicant.nacteResultDetails','student.applicant.nectaResultDetails','student.campusProgram.program',
			          'student.applicant.disabilityStatus','student.applicant.country','student.applicant.region','student.applicant.ward'])
			  ->where('status','REGISTERED')
			  ->where('study_academic_year_id', $request->get('study_academic_year_id'))
			  ->where('semester_id',session('active_semester_id'))->latest()->get();
			}else{
			  $active_students = Registration::whereHas('student.campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
			  ->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
			  ->whereHas('student.studentshipStatus',function($query){$query->where('name','ACTIVE')->orWhere('name','RESUMED');})
			  ->with(['student.applicant.nextOfKin','student.applicant.nextOfKin.country','student.applicant.nextOfKin.region','student.applicant.nextOfKin.ward','student.applicant.nacteResultDetails','student.applicant.nectaResultDetails','student.campusProgram.program',
			          'student.applicant.disabilityStatus','student.applicant.country','student.applicant.region','student.applicant.ward'])
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
		   $students = Auth::user()->hasRole('hod')? Registration::whereHas('student.campusProgram.program.departments',function($query) use($staff){
				  $query->where('id',$staff->department_id);
			})->whereHas('student.studentshipStatus',function($query){
				  $query->where('name','ACTIVE');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get() : Registration::whereHas('student.studentshipStatus',function($query){
				  $query->where('name','ACTIVE');
			})->with(['student.campusProgram.program'])->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->get();
		   $callback = function() use ($students) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle, ['Name','Sex','Registration Number','Program']);
                  foreach ($students as $row) { 
                      fputcsv($file_handle, [$row->student->first_name.' '.$row->student->middle_name.' '.$row->student->surname,$row->student->gender,$row->student->registration_number,$row->student->campusProgram->program->name]);
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

			$student = Student::whereHas('registrations', function($query) use($request, $semester){$query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$semester->id)->where('id_print_status', 0);})
					   ->whereHas('campusProgram.program',function($query) use($request){$query->where('award_id',$request->get('program_level_id'));})
					   ->whereHas('applicant',function($query) use($request){$query->where('campus_id',$request->get('campus_id'));})
					   ->with('applicant','campusProgram.program','campusProgram.campus')->latest()->get();
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
            'request'=>$request
        ];
        return view('dashboard.registration.id-card',$data)->withTitle('ID Card');
    }


    /**
     * Show ID Card
     */
    public function showIDCard(Request $request)
    {
        $student = Student::with('campusProgram.program','campusProgram.campus')->where('registration_number',$request->get('registration_number'))->first();
        $ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $registration = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first();
        if(!$registration){
             return redirect()->back()->with('error','Student has not been registered for this semester');
        }

        $id_requests = IdCardRequest::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('is_printed',0)->get();

        if(count($id_requests) == 0 && $registration->id_print_status != 0){
            return redirect()->back()->with('error','Student ID already printed');
        }
        $registration->id_print_status = 1;
        $registration->save();

        IdCardRequest::where('study_academic_year_id',$ac_year->id)->where('student_id',$student->id)->where('is_printed',0)->update(['is_printed'=>1]);

        if(!$student->image){
             return redirect()->back()->with('error','Student image is missing');
        }
        if(!$student->signature){
             return redirect()->back()->with('error','Student signature is missing');
        }
        $data = [
            'student'=>$student,
            'semester'=>$semester,
            'study_academic_year'=>$ac_year
        ];
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
               // 'format'=>[500,400]
        ]);
        return  $pdf->stream(); 
         // return view('dashboard.registration.reports.id-card',$data);
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

}
