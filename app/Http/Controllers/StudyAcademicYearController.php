<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Actions\StudyAcademicYearAction;
use App\Utils\Util;
use Validator;

class StudyAcademicYearController extends Controller
{
     /**
     * Display a list of study academic years
     */
    public function index()
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->paginate(20),
           'academic_years'=>AcademicYear::all()
    	];
    	return view('dashboard.academic.study-academic-years',$data)->withTitle('Study Academic Years');
    }

    /**
     * Store study academic year into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'begin_date'=>'required',
            'end_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        (new StudyAcademicYearAction)->store($request);

        return Util::requestResponse($request,'Study academic year updated successfully');
    }

    /**
     * Update specified academic year
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'begin_date'=>'required',
            'end_date'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new StudyAcademicYearAction)->update($request);

        return Util::requestResponse($request,'Study academic year updated successfully');
    }
    
    /**
     * Display asssigned programs
     */
    public function showPrograms(Request $request)
    {
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with(['campusPrograms'])->paginate(20),
           'programs'=>Program::all()
    	];
    	return view('dashboard.academic.assign-academic-year-programs',$data)->withTitle('Academic Year Programs');
    }

    /**
     * Update asssigned programs
     */
    public function updatePrograms(Request $request)
    {
    	$programs = Program::all();
    	$year = StudyAcademicYear::find($request->get('study_academic_year_id'));
        $programIds = [];
        foreach ($programs as $program) {
        	if($request->has('year_'.$year->id.'_campus_program_'.$program->id)){
        		$programIds[] = $request->get('year_'.$year->id.'_campus_program_'.$program->id);
        	}
        }

        if(count($programIds) == 0){
            return redirect()->back()->with('error','Please select programs to assign');
        }else{
        	$year->programs()->sync($programIds);

    	    return redirect()->back()->with('message','Campus programs assigned successfully');
        }
    }

    /**
     * Remove the specified study academic year
     */
    public function destroy(Request $request, $id)
    {
        try{
            $academic_year = StudyAcademicYear::findOrFail($id);
            $academic_year->delete();
            return redirect()->back()->with('message','Study academic year deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
