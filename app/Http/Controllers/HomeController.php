<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExamRequest;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Registration\Models\Registration;
use App\Models\User;
use App\Models\UserSession;
use Auth;
use App\Domain\Application\Models\InternalTransfer;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\CampusProgram;

class HomeController extends Controller
{
    /**
     * Display login page
     */
    public function index()
    {
    	return view('auth.login')->withTitle('Login');
    }

    /**
     * Display login page
     */
    public function dashboard(Request $request)
    {
		$staff = User::find(Auth::user()->id)->staff;
        $ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
		//$loan_beneficiaries = Student::whereHas('registrations', function($query) use($ac_year){$query->where('study_academic_year_id', $ac_year->id);})
		$internal_trasnfers = InternalTransfer::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
												->whereNull('loan_changed')->where('status','SUBMITTED')
												->whereHas('student.registrations',function($query) use($ac_year){$query->where('study_academic_year_id', $ac_year->id);})->get();
		$postponements = Postponement::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									   ->whereNotNull('postponed_by_user_id')->where('status', '!=', 'DECLINED')
									   ->where('category','!=','EXAM')->where('study_academic_year_id',$ac_year->id)->get();				
		$loan_beneficiary = LoanAllocation::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
											->where('study_academic_year_id', $ac_year->id)->get();
		$deceased = Student::whereHas('applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
							 ->whereHas('studentshipStatus',function($query){$query->where('name', 'DECEASED');})->get();	

        $beneficiaries = array();
		$loan_beneficiary_count = 0;
/* 		foreach($internal_trasnfers as $transfers){
			$loan_beneficiary = LoanAllocation::where('student_id', $transfers->student_id)->where('study_academic_year_id', $ac_year->id)->first();
			if($loan_beneficiary){
				$loan_beneficiary_count = 1;
				$beneficiaries[] = $loan_beneficiary;
			}
		} */

		foreach($loan_beneficiary as $beneficiary){
			if($postponements){
				foreach($postponements as $post){
					if($beneficiary->student_id == $post->student_id){
						$loan_beneficiary_count = 1;	
						break;
					}				
				}				
			}
			if($deceased){
				foreach($deceased as $death){
					if($beneficiary->student_id == $death->id){
						$loan_beneficiary_count = 1;
						break;						
					}
				}				
			}
			if($internal_trasnfers){
				foreach($internal_trasnfers as $transfers){
					if($beneficiary->student_id == $transfers->student_id){
						$loan_beneficiary_count = 1;
						break;	
					}				
				}				
			}			
		}		

        $data = [
           'staff'=>$staff,
           'postponements_arc_count'=>Postponement::whereNull('postponed_by_user_id')->count(),
           'resumptions_arc_count'=>Postponement::whereNotNull('postponed_by_user_id')->whereNull('resumed_by_user_id')->count(),
           'special_exams_arc_count'=>SpecialExamRequest::whereNull('approved_by_user_id')->count(),
           'postponements_hod_count'=>Postponement::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->whereHas('student.campusProgram.program.departments', function($query) use($staff){$query->where('department_id', $staff->department_id);})
									->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->whereNull('recommended_by_user_id')->count(),
           'special_exams_hod_count'=>SpecialExamRequest::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->whereHas('student.campusProgram.program.departments', function($query) use($staff){$query->where('department_id', $staff->department_id);})						
									->whereHas('student.campusProgram', function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->whereNull('recommended_by_user_id')->count(),
           'postponements_count'=>Postponement::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
								->whereNotNull('postponed_by_user_id')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
           'last_postponement'=>Postponement::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
							  ->whereNotNull('postponed_by_user_id')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->latest()->first(),
           'deceased_count'=>Registration::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
						   ->whereHas('student.studentshipStatus',function($query){$query->where('name','DECEASED');})
						   ->where('study_academic_year_id',session('active_academic_year_id'))
						   ->where('semester_id',session('active_semester_id'))->count(),
           'last_deceased'=>Registration::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
						  ->whereHas('student.studentshipStatus',function($query){$query->where('name','DECEASED');})
						  ->where('study_academic_year_id',session('active_academic_year_id'))
						  ->where('semester_id',session('active_semester_id'))->latest()->first(),
           'last_session'=>UserSession::where('user_id',Auth::user()->id)->orderBy('last_activity','desc')->offset(1)->first(),
		   'internal_transfer_count'=>InternalTransfer::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->where('status','SUBMITTED')->count(),
		   'last_internal_transfer'=>InternalTransfer::whereHas('student.applicant',function($query) use($staff){$query->where('campus_id',$staff->campus_id);})
									->where('status','SUBMITTED')->latest()->first(),
		   'loan_beneficiary_count'=>$loan_beneficiary_count
        ];
		return $data->postponements_hod_count.' '.$data->special_exams_hod_count;
		
    	return view('dashboard',$data)->withTitle('Home');
    }
}
