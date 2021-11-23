<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Group;
use App\Domain\Academic\Models\Stream;
use App\Domain\Academic\Models\Department;
use App\Domain\Registration\Models\Registration;
use App\Utils\Util;
use Validator, PDF;

class GroupController extends Controller
{
	 /**
     * Store group into database
     */
    public function store(Request $request)
    {
            $stream = Stream::find($request->get('stream_id'));
            $group_stud_quotient = intdiv($stream->number_of_students,$request->get('number_of_groups'));
            $group_stud_remainder = $stream->number_of_students%$request->get('number_of_groups');
            for($i = 1; $i <= $request->get('number_of_groups'); $i++){
            	if($i == 1){
                   $rm_group = new Group;
                   $rm_group->name = $i;
            	   $rm_group->number_of_students = $group_stud_remainder+$group_stud_quotient;
            	   $rm_group->stream_id = $stream->id;
            	   $rm_group->save();
            	}else{
                   $rm_group = new Group;
                   $rm_group->name = $i;
            	   $rm_group->number_of_students = $group_stud_quotient;
            	   $rm_group->stream_id = $stream->id;
            	   $rm_group->save();
            	}

            	Registration::where('year_of_study',$stream->year_of_study)->where('study_academic_year_id',$stream->study_academic_year_id)->where('stream_id',$stream->id)->where('group_id',0)->take($rm_group->number_of_students)->update(['group_id'=>$rm_group->id]);
            }
   
            return redirect()->back()->with('message','Groups created successfully');
    }

    /**
     * Show attendance
     */
    public function showAttendance(Request $request, $id)
    {
    	try{
    		$group = Group::with(['stream.studyAcademicYear.academicYear','stream.campusProgram.program','stream.campusProgram.campus'])->findOrFail($id);
	    	$data = [
	           'registrations'=>Registration::with('student')->where('group_id',$id)->get(),
	           'group'=>$group,
	           'department'=>Department::findOrFail($group->stream->campusProgram->program->department_id)
	    	];
    	    $pdf = PDF::loadView('dashboard.academic.reports.students-in-group', $data)->setPaper('a4','landscape');
            return $pdf->stream();
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

	/**
     * Remove group from database
     */
    public function destroy($id)
    {
    	try{
            $group = Group::findOrFail($id);
            $group->delete();

            $stream = Stream::find($group->stream_id);

            Registration::where('year_of_study',$stream->year_of_study)->where('study_academic_year_id',$stream->study_academic_year_id)->where('stream_id',$stream->id)->update(['group_id'=>0]);

            $remaining_groups = Group::where('stream_id',$stream->id)->get();
            $group_stud_quotient = count($remaining_groups) != 0 ? intdiv($stream->number_of_students,count($remaining_groups)) : 0;
            $group_stud_remainder = count($remaining_groups) != 0 ? $stream->number_of_students%count($remaining_groups) : 0;
            foreach($remaining_groups as $key=>$gr){
            	if($key == 0){
                   $rm_group = Group::find($gr->id);
            	   $rm_group->number_of_students = $group_stud_remainder+$group_stud_quotient;
            	   $rm_group->save();
            	}else{
                   $rm_group = Group::find($gr->id);
            	   $rm_group->number_of_students = $group_stud_quotient;
            	   $rm_group->save();
            	}
            }
           
            $remaining_groups = Group::where('stream_id',$stream->id)->get();
            if(count($remaining_groups) == 0){
            	Registration::where('year_of_study',$stream->year_of_study)->where('study_academic_year_id',$stream->study_academic_year_id)->where('stream_id',$stream->id)->update(['group_id'=>0]);
            }else{
            
	            foreach ($remaining_groups as $key => $group) {
	            	Registration::where('year_of_study',$stream->year_of_study)->where('study_academic_year_id',$stream->study_academic_year_id)->where('stream_id',$stream->id)->take($group->number_of_students)->where('group_id',0)->update(['group_id'=>$group->id]);
	            }
            }
                     
            return redirect()->back()->with('message','Groups deleted successfully');
    	}catch(\Exception $e){
    		return redirect()->back()->with('error','Unable to get the resource specified in this request');
    	}
    }
}