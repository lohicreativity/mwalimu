<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\SpecialExamRequest;
use App\Domain\Academic\Models\Postponement;
use App\Models\User;
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
        $data = [
           'staff'=>User::find(Auth::user()->id)->staff,
           'postponements_arc_count'=>Postponement::whereNotNull('postponed_by_user_id')->count(),
           'special_exams_arc_count'=>SpecialExamRequest::whereNotNull('approved_by_user_id')->count(),
           'postponements_hod_count'=>Postponement::whereNotNull('recommended_by_user_id')->count(),
           'special_exams_hod_count'=>SpecialExamRequest::whereNotNull('recommended_by_user_id')->count()
        ];
    	return view('dashboard',$data)->withTitle('Home');
    }
}
