<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\Semester;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Application\Models\Applicant;
use App\Utils\SystemLocation;
use App\Models\User;
use App\Mail\LoanAllocationCreated;
use Auth, Mail;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Registration\Models\Student;
use App\Domain\Registration\Models\StudentshipStatus;
use Validator;

class LoanAllocationController extends Controller
{
    /**
     * Display form for uploading loan allocations
     */
    public function index(Request $request)
    {
    	$staff = User::find(Auth::user()->id)->staff;
        $data = [
            'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'staff'=>$staff,
            'request'=>$request
        ];
        return view('dashboard.finance.upload-loan-allocations',$data)->withTitle('Upload Loan Allocation');
    }

    /**
     * Download loan allocations template
     */
    public function downloadAllocationTemplate()
    {
        try{
           return response()->download(public_path().'/uploads/loan_allocation_template.csv');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Document could not be found');
        }
    }
    /**
     * Upload loan allocations
     */
    public function uploadAllocations(Request $request)
    {   ini_set('memory_limit', '-1');
        set_time_limit(180);
		
		$validation = Validator::make($request->all(),[
            'allocations_file'=>'required|mimes:csv,txt'
         ]);

         if($validation->fails()){
             if($request->ajax()){
                return response()->json(array('error_messages'=>$validation->messages()));
             }else{
                return redirect()->back()->withInput()->withErrors($validation->messages());
             }
         }

    	if($request->hasFile('allocations_file')){
			$staff = User::find(Auth::user()->id)->staff;
			$study_academic_year = StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id'));
			$destination = public_path('loan_allocations/'); //public_path().'/loan_allocations/';

			$request->file('allocations_file')->move($destination, $request->file('allocations_file')->getClientOriginalName());
			$file_name = SystemLocation::renameFile($destination, $request->file('allocations_file')->getClientOriginalName(),'csv', $study_academic_year->academicYear->year.'_'.Auth::user()->id.'_'.now()->format('YmdHms'));

			$uploaded_loans = [];
			$csvFileName = $file_name;
			$csvFile = $destination.$csvFileName;
			$file_handle = fopen($csvFile, 'r');
			fgetcsv($file_handle);
			while (!feof($file_handle)) {
				$line_of_text[] = fgetcsv($file_handle, 0, ',');
			}

			fclose($file_handle);
			$existing_beneficiaries = $missallocations = [];

			foreach($line_of_text as $line){
				if(gettype($line) != 'boolean'){
					$index_number = str_replace('.','/',trim($line[1]));

					$applicant = Applicant::where('index_number',$index_number)->where('campus_id',$staff->campus_id)->latest()->first();

					if($applicant){
						$student = Student::where('applicant_id')->latest()->first();
						$firstname = $middlename = $surname = null;
						
						if($student){
							$firstname = $student->first_name;
							$middlename = $student->middle_name;
							$surname = $student->surname;
							$sex = $student->sex;
							$phone = $student->phone;
							$year_of_study = $student->year_of_study;
							
						}
						if(LoanAllocation::where('applicant_id',$applicant->id)->where('study_academic_year_id',$study_academic_year->academicYear->id)->where('batch_no',trim($line[3]))->first()){
							$existing_beneficiaries[] = trim($line[1]);
							continue;
						}else{
							$allocation = new LoanAllocation;
							$allocation->applicant_id = $applicant->id;
							$allocation->student_id = $student? $student->id : null;
							$allocation->index_number = $index_number;
							$allocation->campus_id = $applicant->campus_id;
							$allocation->first_name = $student? $firstname : $applicant->first_name;
							$allocation->middle_name = $student? $middlename : $applicant->middle_name;
							$allocation->surname = $student? $surname : $applicant->surname;
							$allocation->sex = $student? $sex : $applicant->gender;
							$allocation->phone = $student? $phone : $applicant->phone;
							$allocation->year_of_study = $student? $year_of_study : trim($line[2]);
							$allocation->study_academic_year_id = $study_academic_year->academicYear->id;
							$allocation->batch_no = trim($line[3]);
							$allocation->tuition_fee = trim($line[4]);	
							$allocation->meals_and_accomodation = trim($line[5]);			
							$allocation->books_and_stationeries = trim($line[6]);	
							$allocation->research = trim($line[7]);		
							$allocation->field_training = trim($line[8]);	
							$allocation->uploaded_by_user_id = $staff->id;			
							$allocation->uploaded_at = now();								
							$allocation->save();
						}
						
					}else{
						$missallocations[] =  trim($line[1]);
					}
				}
			}
			if(count($missallocations) > 0){
				session()->flash('missallocations',$missallocations);
				return redirect()->back()->with('error','The following beneficiaries are not our students');
			}
			if(count($existing_beneficiaries) > 0){
				session()->flash('existing_beneficiaries',$existing_beneficiaries);
				return redirect()->back()->with('error','The following students have allocations in this batch');
			}

			return redirect()->to('finance/loan-beneficiaries')->with('message','Loan allocations uploaded successfully');
    	}
    }

    /**
     * Show loan beneficiaries
     */
    public function showLoanBeneficiaries(Request $request)
    {
		$staff = User::find(Auth::user()->id)->staff;
		$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
		$internal_trasnfers = InternalTransfer::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
		->whereNull('loan_changed')->where('status','SUBMITTED')->with('previousProgram.program','currentProgram.program')
		->whereHas('student.registrations',function($query) use($ac_year){$query->where('study_academic_year_id', $ac_year->id);})->get();
		$postponements = Postponement::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
		->whereNotNull('recommended_by_user_id')->where('status', '!=', 'DECLINED')
		->where('category','!=','EXAM')->where('study_academic_year_id', $ac_year->id)->latest()->get();
		$loan_beneficiary = LoanAllocation::where('campus_id',$staff->campus_id)->where('study_academic_year_id', $ac_year->id)->get();
		$deceased = Student::whereHas('applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
		->whereHas('studentshipStatus',function($query){$query->where('name', 'DECEASED');})->get();
		
        $beneficiaries = $stud_transfers = $stud_postponements = $stud_deceased = array();	

		foreach($loan_beneficiary as $beneficiary){
			if($postponements){
				foreach($postponements as $post){
					if($beneficiary->student_id == $post->student_id){
						$stud_postponements[]= $post;
						$beneficiaries[] = $beneficiary;	
					}				
				}				
			}
			if($deceased){
				foreach($deceased as $death){
					if($beneficiary->student_id == $death->id){
						$stud_deceased[]= $death;
						$beneficiaries[] = $beneficiary;						
					}
				}				
			}
			if($internal_trasnfers){
				foreach($internal_trasnfers as $transfers){
					if($beneficiary->student_id == $transfers->student_id){
						$beneficiaries[] = $beneficiary;
						$stud_transfers[]= $transfers;
					}				
				}				
			}

		}

		if(empty($request)){
			$loan_beneficiaries = LoanAllocation::where('study_academic_year_id',$ac_year->id)->where('campus_id',$staff->campus_id)->where('year_of_study',1)->get();
		}else{
			$loan_beneficiaries = LoanAllocation::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_id',$staff->campus_id)
											  ->where('year_of_study',$request->get('year_of_study'))->get();
		}

    	$data = [
    		'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'beneficiaries'=>$request->get('loan_status') == 1? $beneficiaries : $loan_beneficiaries,
			'transfers'=>$stud_transfers? $stud_transfers : [],
			'postponements'=>$stud_postponements? $stud_postponements : [],
			'deceased'=>$stud_deceased? $stud_deceased : [],
            'request'=>$request
    	];
    	if($request->get('year_of_study') && count($data['beneficiaries']) == 0){
    		return redirect()->back()->with('error','No Loan allocations available');
    	}
    	return view('dashboard.finance.loan-beneficiaries',$data)->withTitle('Loan Beneficiaries');
    }

    /**
     * Download Loan Beneficiaries
     */
    public function downloadLoanBeneficiaries(Request $request)
    {	
		$staff = User::find(Auth::user()->id)->staff;
		$ac_year = StudyAcademicYear::where('id',$request->study_academic_year_id)->with('academicYear')->first();
        $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=Loans-Beneficiaries'.'- Yr-'.$request->year_of_study.'-'.$ac_year->academicYear->year.'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

        $list = LoanAllocation::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
		->with(['student','student.campusProgram.program'])->where('study_academic_year_id', $request->study_academic_year_id)->where('year_of_study',$request->year_of_study)->get();
		if($list){
			$callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  fputcsv($file_handle,['Form Four Index','Student Name','Sex','Registration Number','Course Code','Year of Study','Bank Account Number','Bank Name','Batch','Remarks']);
                  foreach ($list as $row) {
                    fputcsv($file_handle, [$row->student->applicant->index_number,$row->student->surname.', '.$row->student->first_name.' '.$row->student->middle_name,$row->student->gender == 'M'? 'Male' : 'Female'
					,$row->student->registration_number,$row->student->campusProgram->code,$row->student->year_of_study,$row->account_number, $row->bank_name, 1, null]);
                  }
                  fclose($file_handle);
              };
        return response()->stream($callback, 200, $headers);			  
		}
    }
	
    public function updateLoanBeneficiaries(Request $request)
    {
		$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();

		if($request->get('postponement_status') == 1 || $request->get('deceased_status') == 1){
			LoanAllocation::where('student_id', $request->get('student_id'))->where('study_academic_year_id', $ac_year->id)->latest()->delete();
			return redirect()->back()->with('message','Successfully removed loan allocation');					
		}elseif($request->get('transfer_status') == 1){
			$transfer = InternalTransfer::where('student_id',$request->get('student_id'))->whereNull('loan_changed')->latest()->first();
			$transfer->loan_changed = 1;
			$transfer->save();
			return redirect()->back()->with('message','Successfully changed loan allocation');			
		}
    }

     /**
     * Show loan beneficiaries bank details
     */
    public function showLoanBankDetails(Request $request)
    {
    	$data = [
    		'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'beneficiaries'=>LoanAllocation::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->get(),
            'request'=>$request
    	];
    	if($request->get('year_of_study') && count($data['beneficiaries']) == 0){
    		return redirect()->back()->with('error','No Loan allocations available');
    	}
    	return view('dashboard.finance.loan-allocations',$data)->withTitle('Loan Beneficiaries');
    }

    /**
     * Update loan signatures
     */
    public function updateSignatures(Request $request)
    {
    	$staff = User::find(Auth::user()->id)->staff;
    	$loans = LoanAllocation::with('student')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->get();

		foreach($loans as $loan){
    		if($request->get('allocation_'.$loan->id) == $loan->id){
    			$ln = LoanAllocation::find($loan->id);
    			$ln->has_signed = 1;
    			$ln->save();
				return $loan->applicant_id;
				$applicant = null;
				if($loan->student->applicant_id){
					$applicant = Applicant::find($loan->student->applicant_id);

				}else{
					$applicant = Applicant::find($loan->applicant_id);
				}

    			$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
                $semester = Semester::where('status','ACTIVE')->first();
                if($applicant->insurance_check == 1){
	    			if($reg = Registration::where('student_id',$loan->student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
	                    $registration = $reg;
		            }else{
		              $registration = new Registration;
		            }
		            $registration->study_academic_year_id = $ac_year->id;
		            $registration->semester_id = $semester->id;
		            $registration->student_id = $loan->student->id;
		            $registration->year_of_study = 1;
		            $registration->registered_by_staff_id = $staff->id;
		            $registration->status = 'REGISTERED';
		            $registration->save();
		        }
    		}
    	}
    	return redirect()->back()->with('message','Signatures updated successfully');
    }

    /**
     * Notify loan students
     */
    public function notifyLoanStudents(Request $request)
    {
    	$loans = LoanAllocation::with('student')->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->get();
    	foreach($loans as $loan){
    		try{
    			$ln = LoanAllocation::find($loan->id);
    			$ln->notification_sent = 1;
    			$ln->save();

    			$user = new User;
    			$user->username = $loan->name;
    			$user->email = $loan->student->email;
                Mail::to($user)->queue(new LoanAllocationCreated($user));
    		}catch(\Exception $e){}
    	}
    	return redirect()->back()->with('message','Notification sent successfully');
    }
}
