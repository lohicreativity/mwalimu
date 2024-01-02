<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
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
    	// $years = StudyAcademicYear::all();
    	// foreach($years as $year){
    	// 	if(strtotime(now()->format('Y-m-d')) > strtotime($year->end_date)){
    	// 		StudyAcademicYear::where('id',$year->id)->update(['status'=>'INACTIVE']);
    	// 	}
    	// }
    	$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->paginate(20),
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
            'academic_year_id'=>'required',
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

        if(AcademicYear::where('year',$request->get('academic_year_id'))->count() != 0){
            return redirect()->back()->with('error','Study academic year aleady exists');
        }

        if(strtotime($request->get('begin_date')) > strtotime($request->get('end_date'))){
			return redirect()->back()->with('error','End date cannot be less than begin date');
		}elseif(strtotime($request->get('begin_date')) < strtotime(now()->format('Y-m-d'))){
			return redirect()->back()->with('error','Begin date cannot be less than today date');
		}elseif(strtotime($request->get('end_date')) < strtotime(now()->format('Y-m-d'))){
            return redirect()->back()->with('error','End date cannot be less than today date');
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

        $ac_yr = StudyAcademicYear::latest()->first();

        if($ac_yr->begin_date != date('Y-m-d',strtotime($request->get('begin_date'))) && strtotime($request->get('begin_date')) > strtotime($request->get('end_date'))){
			return redirect()->back()->with('error','End date cannot be less than begin date');
		}elseif($ac_yr->begin_date != date('Y-m-d',strtotime($request->get('begin_date'))) && strtotime($request->get('begin_date')) < strtotime(now()->format('Y-m-d'))){
			return redirect()->back()->with('error','Begin date cannot be less than today date');
		}elseif($ac_yr->begin_date != date('Y-m-d',strtotime($request->get('begin_date'))) && strtotime($request->get('end_date')) < strtotime(now()->format('Y-m-d'))){
            return redirect()->back()->with('error','End date cannot be less than today date');
        }


        (new StudyAcademicYearAction)->update($request);

        return Util::requestResponse($request,'Study academic year updated successfully');
    }
    
    /**
     * Display asssigned programs
     */
    public function showPrograms(Request $request)
    {
    	$campusPrograms = CampusProgram::where('campus_id',$request->get('campus_id'))->get();
    	$campusProgramIds = [];
    	foreach($campusPrograms as $prog){
    		$campusProgramIds[] = $prog->id;
    	}
    	$data = [
           'study_academic_years'=>StudyAcademicYear::get(),
           'campuses'=>Campus::all(),
           'campusPrograms'=>CampusProgram::with('program')->get(),
           'campus'=>$request->has('campus_id')? Campus::find($request->get('campus_id')) : null
    	];
    	return view('dashboard.academic.assign-study-academic-year-campus-programs',$data)->withTitle('Study Academic Year Campus Programs');
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
        	if($request->has('year_'.$year->id.'_program_'.$program->id)){
        		$programIds[] = $request->get('year_'.$year->id.'_program_'.$program->id);
        	}
        }

        if(count($programIds) == 0){
            return redirect()->back()->with('error','Please select programs to assign');
        }else{
        	$year->campusPrograms()->sync($programIds);

    	    return redirect()->back()->with('message','Campus programs assigned successfully');
        }
    }

    /**
     * Activate study academic year
     */
    public function activate($id)
    {
    	try{
    		$academic_year = StudyAcademicYear::findOrFail($id);
    		// if(strtotime(now()->format('Y-m-d')) > strtotime($academic_year->begin_date)){
    		// 	return redirect()->back()->with('error','Academic year must be within current dates');
    		// }
            $academic_year->status = 'ACTIVE';
            $academic_year->save();

            StudyAcademicYear::where('id','!=',$academic_year->id)->update(['status'=>'INACTIVE']);

            return redirect()->back()->with('message','Study academic year activated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Deactivate study academic year
     */
    public function deactivate($id)
    {
    	try{
    		$academic_year = StudyAcademicYear::findOrFail($id);
            $academic_year->status = 'INACTIVE';
            $academic_year->save();

            return redirect()->back()->with('message','Study academic year deactivated successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Enable NHIF
     */
    public function enableNhif($id)
    {
        try{
            $academic_year = StudyAcademicYear::findOrFail($id);
            if($academic_year->status == 'ACTIVE'){
                return redirect()->back()->with('error','You cannot enable insurance status because academic year is already active');
            }
            $academic_year->nhif_enabled = 1;
            $academic_year->save();

            return redirect()->back()->with('message','NHIF enabled successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Disable NHIF
     */
    public function disableNhif($id)
    {
        try{
            $academic_year = StudyAcademicYear::findOrFail($id);
            if($academic_year->status == 'ACTIVE'){
                return redirect()->back()->with('error','You cannot disable insurance status because academic year is already active');
            }
            $academic_year->nhif_enabled = 0;
            $academic_year->save();

            return redirect()->back()->with('message','NHIF disabled successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
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
