<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Finance\Models\LoanAllocation;
use App\Utils\SystemLocation;
use App\Models\User;
use App\Mail\LoanAllocationCreated;
use Auth, Mail;

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
     * Upload loan allocations
     */
    public function uploadAllocations(Request $request)
    {
    	if($request->hasFile('allocations_file')){
    		  $study_academic_year = StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id'));
    		  $destination = public_path().'/loan_allocations/';
    		  $request->file('allocations_file')->move($destination, $request->file('allocations_file')->getClientOriginalName());

              $file_name = SystemLocation::renameFile($destination, $request->file('allocation_file')->getClientOriginalName(),'csv', $study_academic_year->academicYear->year.'_'.Auth::user()->id.'_'.now()->format('YmdHms'));

              $uploaded_loans = [];
              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                 $stud = Student::with('applicant')->where('registration_number',trim($line[0]))->first();
                 $allocation = new LoanAllocation;
                 $allocation->index_number = trim($line[1]);
                 $allocation->registration_number = trim($line[2]);
                 $allocation->save();
              }
    	}
    	return redirect()->back()->with('message','Loan allocations uploaded successfully');
    }

    /**
     * Show loan beneficiaries
     */
    public function showLoanBeneficiaries(Request $request)
    {
    	$data = [
    		'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'beneficiaries'=>LoanAllocation::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->paginate(20),
            'request'=>$request
    	];
    	if($request->get('year_of_study') && count($data['beneficiaries']) == 0){
    		return redirect()->back()->with('error','No Loan allocations available');
    	}
    	return view('dashboard.finance.loan-beneficiaries',$data)->withTitle('Loan Beneficiaries');
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

    			$ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
                $semester = Semester::where('status','ACTIVE')->first();

    			if($reg = Registration::where('student_id',$student->id)->where('study_academic_year_id',$ac_year->id)->where('semester_id',$semester->id)->first()){
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
    			$user = new User;
    			$user->username = $loan->name;
    			$user->email = $loan->student->email;
                Mail::to($user)->send(new LoanAllocationCreated($user));
    		}catch(\Exception $e){}
    	}
    }
}
