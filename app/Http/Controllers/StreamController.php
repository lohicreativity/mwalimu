<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Stream;
use App\Domain\Academic\Models\StreamComponent;
use App\Domain\Registration\Models\Registration;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Actions\StreamAction;
use App\Utils\Util;
use Validator, PDF;

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
            'stream_component_id'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }
         
        $component = StreamComponent::find($request->get('stream_component_id'));
        $sum = 0;
        for($i = 1; $i <= $component->number_of_streams; $i++){
           $sum += $request->get('number_'.$i.'_component_'.$component->id);
        }
        if($sum != $component->number_of_students){
        	return redirect()->back()->withInput()->with('error','Number of students must be equal to '.$component->number_of_students);
        }

        (new StreamAction)->store($request);

        // return Util::requestResponse($request,'Streams created successfully');
        return redirect()->to('academic/streams?study_academic_year_id='.$request->get('study_academic_year_id'))->with('message','Streams created successfully');
    }


    /**
     * Reset streams
     */
    public function resetStreams(Request $request)
    {
    	StreamComponent::where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$request->get('campus_program_id'))->delete();

    	Stream::where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('campus_program_id',$request->get('campus_program_id'))->delete();

    	Registration::where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->update(['stream_id'=>0]);

    	return redirect()->back()->with('message','Streams reset successfully');
    }

    /**
     * Show attendance
     */
    public function showAttendance(Request $request, $id)
    {
    	$data = [
           'students'=>Registration::with('student')->where('stream_id',$id)->get()
    	];
    	$pdf = PDF::loadView('dashboard.academic.reports.stundents-in-stream', $data)->setPaper('a4','portrait');
            return $pdf->stream();
    }

    /**
     * Remove stream from database
     */
    public function destroy($id)
    {
    	try{
            $stream = Stream::findOrFail($id);
            $stream->delete();

            $component = StreamComponent::find($stream->stream_component_id);
            $streams_number = $component->number_of_stream - 1;
            $component->number_of_streams = $streams_number;
            $component->save();

            $remaining_streams = Stream::where('stream_component_id',$component->id)->get();
            $stream_stud_quotient = count($remaining_streams) != 0? intdiv($component->number_of_students,count($remaining_streams)) : 0;
            $stream_stud_remainder = count($remaining_streams) != 0 ? $component->number_of_students%count($remaining_streams) : 0;
            foreach($remaining_streams as $key=>$st){
            	if($key == 0){
                   $rm_stream = Stream::find($st->id);
            	   $rm_stream->number_of_students = $stream_stud_remainder+$stream_stud_quotient;
            	   $rm_stream->save();
            	}else{
                   $rm_stream = Stream::find($st->id);
            	   $rm_stream->number_of_students = $stream_stud_quotient;
            	   $rm_stream->save();
            	}
            }
           
            $remaining_streams = Stream::where('stream_component_id',$component->id)->get();
            
            foreach ($remaining_streams as $key => $stream) {
            	Registration::where('year_of_study',$component->year_of_study)->where('study_academic_year_id',$component->study_academic_year_id)->take($stream->number_of_students)->update(['stream_id'=>$stream->id]);
            }
                     
            return redirect()->back()->with('message','Stream deleted successfully');
    	}catch(\Exception $e){
    		return redirect()->back()->with('error','Unable to get the resource specified in this request');
    	}
    }
}
