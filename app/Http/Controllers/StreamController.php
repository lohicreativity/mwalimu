<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StreamController extends Controller
{
    /**
     * Display program streams
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with(['academicYear','streams.groups'])->find($request->get('study_academic_year_id')) : null,
           'campuse_programs'=>CampusProgram::with(['program','campus'])->get()
    	];
    	return view('dashboard.academic.program-module-assignments',$data)->withTitle('Program Module Assignment');
    }
}
