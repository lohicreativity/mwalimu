<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StreamComponent;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Actions\StreamComponentAction;
use App\Utils\Util;
use Validator;

class StreamComponentController extends Controller
{
	 /**
     * Display program streams
     */
    public function index(Request $request)
    {
    	$data = [
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with(['academicYear','streamComponents'=>function($query) use ($request){
                  $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
               }])->find($request->get('study_academic_year_id')) : null,
           'campus_program'=>$request->has('campus_program_id')? CampusProgram::with('program')->find($request->get('campus_program_id')) : null,
           'stream_components'=>StreamComponent::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$request->get('campus_program_id'))->get()
    	];
    	return view('dashboard.academic.stream-components',$data)->withTitle('Stream Components');
    }


    /**
     * Store stream component into database
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


        (new StreamComponentAction)->store($request);

        // return Util::requestResponse($request,'Stream components created successfully');
        return redirect()->to('academic/stream-components?study_academic_year_id='.$request->get('study_academic_year_id').'&campus_program_id='.$request->get('campus_program_id'));
    }
}
