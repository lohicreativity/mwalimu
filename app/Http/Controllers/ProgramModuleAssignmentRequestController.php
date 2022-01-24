<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\ProgramModuleAssignmentRequest;
use App\Models\User;
use Auth;


class ProgramModuleAssignmentRequestController extends Controller
{
    /**
     * Display program module assignments
     */
    public function index(Request $request)
    {
    	$staff = User::find(Auth::user()->id)->staff()->with('department')->first();
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'inclusive_modules'=>Module::all(),
           'semesters'=>Semester::all(),
           'staff'=>$staff,
           'requests'=>ProgramModuleAssignmentRequest::whereHas('programModuleAssignment',function($query) use ($request){
           	       $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
               })->whereHas('programModuleAssignment.module',function($query) use ($staff){
           	       $query->where('department_id',$staff->department_id);
               })->whereHas('staff',function($query) use ($staff){
                   $query->where('campus_id',$staff->campus_id);
               })->with(['programModuleAssignment.module','programModuleAssignment.campusProgram.program','user.staff.campus'])->paginate(20),
           'request'=>$request
    	];
    	return view('dashboard.academic.program-module-assignment-requests',$data)->withTitle('Program Module Assignment Requests');
    }
}
