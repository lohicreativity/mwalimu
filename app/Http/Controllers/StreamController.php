<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Stream;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Actions\StreamAction;
use App\Utils\Util;
use Validator;

class StreamController extends Controller
{
    /**
     * Display program streams
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with(['academicYear','streams.groups','campus_programs'])->find($request->get('study_academic_year_id')) : null,
           'campus_programs'=>CampusProgram::with(['program','campus'])->get()
    	];
    	return view('dashboard.academic.campus-program-streams',$data)->withTitle('Campus Program Streams');
    }
}
