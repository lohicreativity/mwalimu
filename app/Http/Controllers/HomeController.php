<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExamRequest;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Models\User;
use App\Models\UserSession;
use Auth;

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
    public function dashboard()
    {
        $ac_year = StudyAcademicYear::where('status','ACTIVE')->first();
        $semester = Semester::where('status','ACTIVE')->first();
        $data = [
           'staff'=>User::find(Auth::user()->id)->staff,
           'postponements_arc_count'=>Postponement::whereNull('postponed_by_user_id')->count(),
           'resumptions_arc_count'=>Postponement::whereNotNull('postponed_by_user_id')->whereNull('resumed_by_user_id')->count(),
           'special_exams_arc_count'=>SpecialExamRequest::whereNull('approved_by_user_id')->count(),
           'postponements_hod_count'=>Postponement::whereNull('recommended_by_user_id')->count(),
           'special_exams_hod_count'=>SpecialExamRequest::whereNull('recommended_by_user_id')->count(),
           'postponement_count'=>Postponement::whereNotNull('postponed_by_user_id')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
           'last_postponement'=>Postponement::whereNotNull('postponed_by_user_id')->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->latest()->first(),
           'deceased_count'=>Registration::whereHas('student.studentshipStatus',function($query){
                  $query->where('name','DECEASED');
            })->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->count(),
           'last_deceased'=>Registration::whereHas('student.studentshipStatus',function($query){
                  $query->where('name','DECEASED');
            })->where('study_academic_year_id',session('active_academic_year_id'))->where('semester_id',session('active_semester_id'))->latest()->first(),
           'last_session'=>UserSession::where('user_id',Auth::user()->id)->latest()->offset(1)->first()
        ];
    	return view('dashboard',$data)->withTitle('Home');
    }
}
