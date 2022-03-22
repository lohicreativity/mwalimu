<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Models\User;
use App\Utils\Util;
use Auth;

class ClearanceController extends Controller
{
    /**
     * Show form for requesting clearance
     */
    public function index(Request $request)
    {
    	$student = User::find(Auth::user()->id)->student;
    	$data = [
           'student'=>$student,
           'registration'=>Registration::where('student_id',$student->id)->latest()->first()
    	];
    	return view('dashboard.student.clearance',$data)->withTitle('Clearance');
    }

    /**
     * Display a list of limits
     */
    public function showList(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'clearances'=>Clearance::with('student')->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNotNull('status')->latest()->paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.clearance-list',$data)->withTitle('Clearance List');
    }

    /**
     * Store clearance 
     */
    public function store(Request $request)
    {
    	if(Clearance::where('student_id',$request->get('student_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
    		return redirect()->back()->with('error','Clearance request already sent');
    	}
    	$clearance = new Clearance;
    	$clearance->student_id = $request->get('student_id');
    	$clearance->study_academic_year_id = $request->get('study_academic_year_id');
    	$clearance->save();

    	return Util::requestResponse($request,'Clearance requested successfully');
    }

    /**
     * Update clearance 
     */
    public function update(Request $request)
    {
    	$clearance = Clearance::find($request->get('clearance_id'));
    	if($request->get('stage') == 'library'){
    		$clearance->library_status = $request->get('status');
    		$clearance->library_comment = $request->get('comment');
    	}
    	if($request->get('stage') == 'finance'){
    		$clearance->finance_status = $request->get('status');
    		$clearance->finance_comment = $request->get('comment');
    	}
    	if($request->get('stage') == 'hostel'){
    		$clearance->hostel_status = $request->get('status');
    		$clearance->hostel_comment = $request->get('comment');
    	}
    	if($request->get('stage') == 'hod'){
    		$clearance->hod_status = $request->get('status');
    		$clearance->hod_comment = $request->get('comment');
    	}
    	if($request->get('stage') == 'stud_org'){
    		$clearance->stud_org_status = $request->get('status');
    		$clearance->stud_org_comment = $request->get('comment');
    	}
    	$clearance->save();

    	return Util::requestResponse($request,'Clearance updated successfully');
    }
}
