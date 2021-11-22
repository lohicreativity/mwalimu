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
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with(['academicYear','streams'=>function($query) use ($request){
                  $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
               },'streams.groups','campusPrograms'])->find($request->get('study_academic_year_id')) : null,
           'campus_programs'=>CampusProgram::with(['program','campus'])->get()
    	];
    	return view('dashboard.academic.streams-and-groups',$data)->withTitle('Streams and Groups');
    }

    /**
     * Store streams into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'number_of_streams'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new StreamAction)->store($request);

        return Util::requestResponse($request,'Streams created successfully');
    }
}
