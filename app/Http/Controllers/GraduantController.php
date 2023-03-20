<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\Graduant;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Models\Currency;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\StudentshipStatus;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use App\Utils\Util;
use App\Exports\GraduantsExport;
use App\Exports\GraduantsCertExport;
use App\Mail\GraduationAlert;
use Mail, Auth;

class GraduantController extends Controller
{
    /**
     * Run graduants
     */
    public function runGraduants(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campus'=>Campus::find($request->get('campus_id')),
           'campuses'=>Campus::all(),
           'nta_levels'=>NTALevel::get(),
           'request'=>$request
    	];
    	return view('dashboard.academic.run-graduants',$data)->withTitle('Run Graduants');
    }


    /**
     * Sort Graduants
     */
    public function sortGraduants(Request $request)
    {
      $staff = User::find(Auth::user()->id)->staff;

      $past_graduants = Student::whereHas('applicant', function($query) use($request){$query->where('campus_id', $request->get('campus_id'));})
						->whereHas('studentshipStatus',function($query){$query->where('name','GRADUANT');
      })->whereHas('annualRemarks',function($query) use($request){
                $query->where('study_academic_year_id','!=',$request->get('study_academic_year_id'));
            })->whereHas('overallRemark', function($query){$query->where('remark', 'PASS');})->get();
			
      foreach($past_graduants as $grad){
          $user = User::find($grad->user_id);
          $user->status = 'INACTIVE';
          $user->save();
      }
      
/*       if(ResultPublication::where('study_academic_year_id',session('active_academic_year_id'))->where('type','SUPP')->where('campus_id',$staff->campus_id)->count() == 0){
          return redirect()->back()->with('error','Supplementary results not published');
      }
      if(Appeal::whereHas('moduleAssignment',function($query){
           $query->where('study_academic_year_id',session('active_academic_year_id'));
      })->where('is_attended',0)->where('is_paid',1)->count() != 0){
         return redirect()->back()->with('error','Appeals not attended completely');
      } */

      $nta_level = NTALevel::with(['programs'])->find($request->get('nta_level_id'));
      $excluded_list = [];
      $graduant_list = [];

      foreach($nta_level->programs as $program){
          	$campus_program = CampusProgram::with('program')->find($request->get('campus_program_id'));
			
          	$students = Student::whereHas('annualRemarks',function($query) use($request){
                $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
            })->with(['annualRemarks','overallRemark','academicStatus'])->whereHas('campusProgram',function($query) use ($program, $request){
                 $query->where('program_id',$program->id)->where('campus_id',$request->get('campus_id'));
            })->where('year_of_study',$program->min_duration)->get();
          	
          	$status = StudentshipStatus::where('name','GRADUANT')->first();
          	foreach($students as $student){
          		if($student->overallRemark){
      	    		$grad = Graduant::where('student_id',$student->id)->first();
      	            if($grad){
						if($grad->overall_remark_id != $student->overallRemark->id){
							$graduant = $grad;
							$graduant->overall_remark_id = $student->overallRemark->id;
							
								if($student->academicStatus->name == 'PASS'){
									   $graduant->status = 'PENDING';
								}else{
								   $graduant->status = 'EXCLUDED';
								}
								$count = 0;
									foreach($student->annualRemarks as $remark){
										if($remark->remark != 'PASS'){
										   $graduant->status = 'EXCLUDED';
									   $excluded_list[] = $student;
									 if($remark->remark == 'POSTPONED'){
									   $graduant->reason = 'Postponed';
									   break;
									 }else{
									   if(str_contains($student->academicStatus->name,'DISCO')){
										   $graduant->reason = 'Failed & Disco';
										   break;
									   }else{
										   $graduant->reason = 'Incomplete Results';
										   break;
									   }
									 }
									   break;
										}
								  $count++;
									}
								if($graduant->status != 'EXCLUDED'){
									$graduant_list[] = $student;
									if($count >= $program->min_duration){
									   if($student->academicStatus->name == 'PASS'){
										   if($cls = Clearance::where('student_id',$student->id)->first()){
											  $clearance = $cls;
										   }else{
											  $clearance = new Clearance;
										   }
										   $clearance->student_id = $student->id;
										   $clearance->study_academic_year_id = $request->get('study_academic_year_id');
										   $clearance->save();
									   }
									}
								}
						}
      	    		}else{
      	               $graduant = new Graduant;
      	    		}
					
      	    		$graduant->student_id = $student->id;
      	    		$graduant->overall_remark_id = $student->overallRemark->id;
      	    		$graduant->study_academic_year_id = $request->get('study_academic_year_id');
                if($student->academicStatus->name == 'PASS'){
      	    		   $graduant->status = 'PENDING';
                }else{
                   $graduant->status = 'EXCLUDED';
                }
                $count = 0;
      	    		foreach($student->annualRemarks as $remark){
      	    			if($remark->remark != 'PASS'){
      	    			   $graduant->status = 'EXCLUDED';
	                   $excluded_list[] = $student;
                     if($remark->remark == 'POSTPONED'){
                       $graduant->reason = 'Postponed';
                       break;
                     }else{
                       if(str_contains($student->academicStatus->name,'DISCO')){
                           $graduant->reason = 'Failed & Disco';
                           break;
                       }else{
                           $graduant->reason = 'Incomplete Results';
                           break;
                       }
                     }
	                   break;
      	    			}
                  $count++;
      	    		}
                if($graduant->status != 'EXCLUDED'){
                    $graduant_list[] = $student;
                    if($count >= $program->min_duration){
                       if($student->academicStatus->name == 'PASS'){
                           if($cls = Clearance::where('student_id',$student->id)->first()){
                              $clearance = $cls;
                           }else{
                              $clearance = new Clearance;
                           }
                           $clearance->student_id = $student->id;
                           $clearance->study_academic_year_id = $request->get('study_academic_year_id');
                           $clearance->save();
                       }
                    }
                }
      	    		$graduant->save();
          	  }
    	    $student = Student::find($student->id);
          if($student->academicStatus->name == 'PASS'){
    	    $student->studentship_status_id = $status->id;
          }
    	    $student->save();
        }
    	}
      if(count($graduant_list) == 0 && count($excluded_list) == 0){
          return redirect()->back()->with('error','No student qualifies to be in the graduants list');
      }

    	return redirect()->back()->with('message','Graduants list created successfully');

    }


    /**
     * Show graduants list
     */
    public function showGraduants(Request $request)
    { 
      if($request->get('query')){
         $graduants = $request->get('campus_id')? Graduant::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->whereHas('student.campusProgram',function($query) use($request){
               $query->where('campus_id',$request->get('campus_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where(function($query){
                  $query->where('status','GRADUATING')->orWhere('status','PENDING');
           })->paginate(50) : Graduant::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where(function($query){
                  $query->where('status','GRADUATING')->orWhere('status','PENDING');
           })->paginate(50);
      }else{
         $graduants = $request->get('campus_id')? Graduant::whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->whereHas('student.campusProgram',function($query) use($request){
               $query->where('campus_id',$request->get('campus_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where(function($query){
                  $query->where('status','GRADUATING')->orWhere('status','PENDING');
           })->paginate(50) : Graduant::whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where(function($query){
                  $query->where('status','GRADUATING')->orWhere('status','PENDING');
           })->paginate(50);
      }
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'graduants'=>$graduants,
           'awards'=>Award::all(),
           'campuses'=>Campus::all(),
           'request'=>$request
    	];
    	return view('dashboard.academic.graduants-list',$data)->withTitle('Graduants List');
    }

    /**
     * Show non graduants list
     */
    public function showExcludedGraduants(Request $request)
    {
      if($request->get('query')){
         $non_graduants = $request->get('campus_id')? Graduant::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->whereHas('student.campusProgram',function($query) use($request){
               $query->where('campus_id',$request->get('campus_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','EXCLUDED')->paginate(50) : Graduant::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
           })->whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','EXCLUDED')->paginate(50);
      }else{
         $non_graduants = $request->get('campus_id')? Graduant::whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->whereHas('student.campusProgram',function($query) use($request){
               $query->where('campus_id',$request->get('campus_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','EXCLUDED')->paginate(50) : Graduant::whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('status','EXCLUDED')->paginate(50);
      }
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'non_graduants'=>$non_graduants,
           'awards'=>Award::all(),
           'campuses'=>Campus::all(),
           'request'=>$request
    	];
    	return view('dashboard.academic.non-graduants-list',$data)->withTitle('Non Graduants List');
    }

    /**
     * Approve graduants
     */
    public function approveGraduants(Request $request)
    {
        $graduants = Graduant::whereHas('student.campusProgram.program',function($query) use($request){
               $query->where('award_id',$request->get('program_level_id'));
           })->whereHas('student.campusProgram',function($query) use($request){
               $query->where('campus_id',$request->get('campus_id'));
           })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

        foreach ($graduants as $graduant) {
           if($request->get('grad_'.$graduant->id) == $graduant->id){
              if($request->get('graduant_'.$graduant->id) == $graduant->id){
                  $grad = Graduant::find($graduant->id);
                  $grad->status = 'GRADUATING';
                  $grad->save();

                  try{
                     $user = new User;
                     $user->email = $graduant->student->email;
                     $user->username = $graduant->student->first_name.' '.$graduant->student->surname;
                     Mail::to($user)->queue(new GraduationAlert($graduant));
                  }catch(\Exception $e){}
              }else{
                  $grad = Graduant::find($graduant->id);
                  $grad->status = 'EXCLUDED';
                  $grad->reason = 'Disapproved';
                  $grad->save();
              }
           }
        }

        return redirect()->back()->with('message','Graduants approved successfully');
    }

    /**
     * Download list
     */
    public function downloadList(Request $request)
    {	
          return (new GraduantsExport($request->get('study_academic_year_id'), $request->get('program_level_id'), $request->get('campus_id')))->download('graduants.xlsx');
    }

    /**
     * Download list
     */
    public function downloadCertList(Request $request)
    {
        return (new GraduantsCertExport($request->get('study_academic_year_id'), $request->get('program_level_id'), $request->get('campus_id')))->download('graduants-certificates.xlsx');
		
    }

    /**
     * Graduation confirmation 
     */
    public function graduationConfirmation(Request $request)
    {   
        $student = User::find(Auth::user()->id)->student;
        $graduant = Graduant::where('student_id',$student->id)->where('status','GRADUATING')->first();
        if(!$graduant){
             return redirect()->back()->with('error','You are not in the graduants list, please check with the Examination Office');
        }
			
        $data = [
           'student'=>$student,
           'graduant'=>$graduant,
		   'payment_status'=> Invoice::whereHas('feeType',function($query){$query->where('name','LIKE','%Graduation Gown%');})->whereHas('GatewayPayment')->where('payable_id', $student->id)
			 ->where('applicable_id', $graduant->study_academic_year_id)->count()
        ];
        return view('dashboard.student.graduation-confirmation',$data)->withTitle('Graduation Confirmation');
    }

    /**
     * Confirm graduation attendance
     */
    public function confirmGraduation(Request $request)
    {
        $graduant = Graduant::with(['student.applicant'])->find($request->get('graduant_id'));
        $graduant->attendance_status = $request->get('status');


        $student = $graduant->student;

        if($request->get('status') == 1){
               $usd_currency = Currency::where('code','USD')->first();

               $graduation_fee = FeeAmount::whereHas('feeItem',function($query){
                  $query->where('name','LIKE','%Graduation Gown%');
               })->where('study_academic_year_id',$graduant->study_academic_year_id)->first();

               if(!$graduation_fee){
				   $graduant->attendance_status = null;
                   return redirect()->back()->with('error','Graduation gown fee amount has not been set');
               }

                if(str_contains($student->applicant->nationality,'Tanzania')){
                  $amount = round($graduation_fee->amount_in_tzs);
                  $currency = 'TZS';
                }else{
                  $amount = round($graduation_fee->amount_in_usd*$usd_currency->factor);
                  $currency = 'TZS';//'USD';
                }
                  $feeType = FeeType::where('name','LIKE','%Graduation Gown%')->first();

                  $invoice = new Invoice;
                  $invoice->reference_no = 'MNMA-GF-'.time();
                  $invoice->actual_amount = $amount;
                  $invoice->amount = $amount;
                  $invoice->currency = $currency;
                  $invoice->payable_id = $student->id;
                  $invoice->payable_type = 'student';
                  $invoice->applicable_id = $graduant->study_academic_year_id;
                  $invoice->applicable_type = 'academic_year';
                  $invoice->fee_type_id = $feeType->id;
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
                                              $student->id,
                                              $student->first_name.' '.$student->surname,
                                              $student->phone,
                                              $student->email,
                                              $generated_by,
                                              $approved_by,
                                              $feeType->duration,
                                              $invoice->currency);
        }
		$graduant->save();
        return redirect()->back()->with('message','Please pay for graduation gown to complete your confirmation');
    }

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
     * Enrollment report
     */
    public function enrollmentReport(Request $request)
    {

        if($request->get('query')){
           $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%')->paginate(50);
        }else{
           $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->paginate(50);
        }

        if($request->get('campus_program_id')){
            $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award'])->where('year_of_study',$request->get('year_of_study'))->where('campus_program_id',$request->get('campus_program_id'))->paginate(50);
        }
        $data = [
           'nta_levels'=>NTALevel::all(),
           'students'=>$students,
           'campus_programs'=>CampusProgram::with('program')->get(),
           'request'=>$request
        ];
        return view('dashboard.academic.enrollment-report',$data)->withTitle('Enrollment Report');
    }

    /**
     * Submit enrolled students
     */
    public function submitEnrolledStudents(Request $request)
    {
        $students = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
           })->with(['applicant.disabilityStatus','campusProgram.program.award','annualRemarks'])->where('year_of_study',$request->get('year_of_study'))->get();


        foreach($students as $student){
            foreach($student->campusProgram->program->departments as $dpt){
              if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                  $department = $dpt;
              }
           }
           $is_year_repeat = 'NO';
           foreach($student->annualRemarks as $remark){
                 if($remark->year_of_study == $student->year_of_study){
                    if($remark->remark == 'CARRY' || $remark->remark == 'RETAKE'){
                       $is_year_repeat = 'YES';
                    }
                 }
           }

           if($student->year_of_study == 1){
              $year_of_study = 'First Year';
           }elseif($student->year_of_study == 2){
              $year_of_study = 'Second Year';
           }elseif($student->year_of_study == 3){
              $year_of_study = 'Third Year';
           }

           // $url='https://api.tcu.go.tz/applicants/submitEnrolledStudents';
            $url="http://41.59.90.200/applicants/submitEnrolledStudents";

               $xml_request = '<?xml version=”1.0” encoding=” UTF-8”?>
                <Request>
                <UsernameToken>
                <Username>'.config('constants.TCU_USERNAME').'</Username>
                <SessionToken>'.config('constants.TCU_TOKEN').'</SessionToken>
                </UsernameToken>
                <RequestParameters>
                <Fname>'.$student->first_name.'</Fname>
                <Mname>'.$student->middle_name.'</Mname>
                <Surname>'.$student->surname.'</Surname>
                <F4indexno>'.$student->applicant->index_number.'</F4indexno>
                <Gender>'.$student->gender.'</Gender>
                <Nationality>'.$student->applicant->nationality.'</Nationality>
                <DateOfBirth>'.date('Y',strtotime($student->applicant->birth_date)).'</DateOfBirth>
                <ProgrammeCategory>'.$student->campusProgram->program->award->name.'</ProgrammeCategory>
                <Specialization>'.$department->name.'</Specialization>
                <AdmissionYear>'.$student->applicant->admission_year.'</AdmissionYear>
                <ProgrammeCode>'.$student->campusProgram->regulator_code.'</ProgrammeCode>
                <RegistrationNumber>'.$student->registration_number.'</RegistrationNumber>
                <ProgrammeName>'.$student->campusProgram->program->name.'</ProgrammeName>
                <YearOfStudy>'.$year_of_study.'</YearOfStudy >
                <StudyMode>'.$student->study_mode.'</StudyMode >
                <IsYearRepeat>'.$is_year_repeat.'</IsYearRepeat >
                <EntryMode>'.$student->applicant->entry_mode.'</EntryMode >
                <Sponsorship>Private</Sponsorship >
                <PhysicalChallenges>'.$student->applicant->disabilityStatus->name.'</PhysicalChallenges>
                </RequestParameters>
                </Request>';
          $xml_response=simplexml_load_string($this->sendXmlOverPost($url,$xml_request));
          $json = json_encode($xml_response);
          $array = json_decode($json,TRUE);

          return dd($xml_request);

          return dd($array);

        }

        return redirect()->back()->with('message','Enrolled students submitted successfully');
    }

    /**
     * Send XML over POST
     */
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

    /**
     * Download enrolled students
     */
    public function downloadEnrolledStudents(Request $request)
    {
         $nta_level = NTALevel::find($request->get('nta_level_id'));
         $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Enrollment-Report-Year-'.$request->get('year_of_study').'-'.$nta_level->name.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

              $list = Student::whereHas('campusProgram.program',function($query) use($request){
                   $query->where('nta_level_id',$request->get('nta_level_id'));
              })->with(['applicant.disabilityStatus','campusProgram.program.award','annualRemarks'])->where('year_of_study',$request->get('year_of_study'))->get();

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['First Name','Middle Name','Surname','Gender','Nationality','Date of Birth','Award Category','Field Specialization','Year of Study','Study Mode','Is Year Repeat','Entry Qualification','Sponsorship','Admission Year','Physical Challenges','F4 Index No','Award Name','Registration Number','Institution Code','Programme Code']);
                  foreach ($list as $student) { 
                       foreach($student->campusProgram->program->departments as $dpt){
                          if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                              $department = $dpt;
                          }
                       }
                       $is_year_repeat = 'NO';
                       foreach($student->annualRemarks as $remark){
                             if($remark->year_of_study == $student->year_of_study){
                                if($remark->remark == 'CARRY' || $remark->remark == 'RETAKE'){
                                   $is_year_repeat = 'YES';
                                }
                             }
                       }

                      fputcsv($file_handle, [$student->first_name,$student->middle_name,$student->surname,$student->gender,
                        $student->applicant->nationality,
                        date('Y',strtotime($student->applicant->birth_date)),
                        $student->campusProgram->program->award->name,
                        $department->name,
                        $student->year_of_study,
                        $student->study_mode,
                        $is_year_repeat,
                        $student->applicant->entry_mode,
                        'Private',
                        $student->applicant->admission_year,
                        $student->applicant->disabilityStatus->name,
                        $student->applicant->index_number,
                        $student->campusProgram->program->name,
                        $student->registration_number,
                        substr($student->campusProgram->regulator_code,0,2),
                        $student->campusProgram->regulator_code,
                        ]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
    }
}
