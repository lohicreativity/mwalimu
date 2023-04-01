<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Models\Clearance;
use App\Domain\Academic\Models\Award;
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
		   'study_academic_year'=>StudyAcademicYear::with('academicYear')->where('status','ACTIVE')->first(),
           'student'=>$student,
           'registration'=>Registration::where('student_id',$student->id)->latest()->first(),
           'clearance'=>Clearance::where('student_id',$student->id)->first()
    	];
    	return view('dashboard.student.clearance',$data)->withTitle('Clearance');
    }

    /**
     * Display a list of limits
     */
    public function showList(Request $request)
    {
        if(Auth::user()->hasRole('hod')){
           $clearances = $request->has('query')? Clearance::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('hod_status')->orWhere('hod_status',0)->latest()->get() : Clearance::whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('hod_status')->orWhere('hod_status',0)->latest()->get();
        }elseif(Auth::user()->hasRole('finance-officer')){
           $clearances = $request->has('query')? Clearance::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('finance_status')->orWhere('finance_status',0)->latest()->get() : Clearance::whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('finance_status')->orWhere('finance_status',0)->latest()->get();
        }elseif(Auth::user()->hasRole('librarian')){
           $clearances = $request->has('query')? Clearance::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('library_status')->orWhere('library_status',0)->latest()->get() : Clearance::whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('library_status')->orWhere('library_status',0)->latest()->get();
        }elseif(Auth::user()->hasRole('dean-of-students')){
           $clearances = $request->get('query')? Clearance::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('hostel_status')->orWhere('hostel_status',0)->latest()->get() : Clearance::whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->whereNull('hostel_status')->orWhere('hostel_status',0)->latest()->get();
        }else{
           $clearances = $request->has('query')? Clearance::whereHas('student',function($query) use($request){
                 $query->where('first_name','LIKE','%'.$request->get('query').'%')->orWhere('middle_name','LIKE','%'.$request->get('query').'%')->orWhere('surname','LIKE','%'.$request->get('query').'%')->orWhere('registration_number','LIKE','%'.$request->get('query').'%');
            })->whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->get() : Clearance::whereHas('student.campusProgram.program',function($query) use($request){
                 $query->where('award_id',$request->get('award_id'));
            })->whereHas('student.studentshipStatus',function($query){
                $query->where('name','ACTIVE')->orWhere('name','GRADUANT');
            })->with(['student.campusProgram.program'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->get();
        }
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'clearances'=>$clearances,
           'awards'=>Award::all(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
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
     * Clear bulk
     */
    public function clearBulk(Request $request)
    {
        $clearances = Clearance::where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

        foreach ($clearances as $clearance) {
            if($request->get('clear_'.$clearance->id) == $clearance->id){
                if($request->get('clearance_'.$clearance->id) == $clearance->id){
                    $clear = Clearance::find($clearance->id);
                    if($request->get('group') == 'hod'){
                        $clear->hod_status = 1;
                    }
                    if($request->get('group') == 'dean-of-students'){
                        $clear->hostel_status = 1;
                    }
                    if($request->get('group') == 'finance-officer'){
                        $clear->finance_status = 1;
                    }
                    if($request->get('group') == 'librarian'){
                        $clear->library_status = 1;
                    }
                    $clear->save();
                }else{
                    $clear = Clearance::find($clearance->id);
                    if($request->get('group') == 'hod'){
                        $clear->hod_status = 0;
                    }
                    if($request->get('group') == 'dean-of-students'){
                        $clear->hostel_status = 0;
                    }
                    if($request->get('group') == 'finance-officer'){
                        $clear->finance_status = 0;
                    }
                    if($request->get('group') == 'librarian'){
                        $clear->library_status = 0;
                    }
                    $clear->save();
                }
            }
        }

        return redirect()->back()->with('message','Clearance list updated successfully');
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
