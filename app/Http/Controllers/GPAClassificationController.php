<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Models\GPAClassification;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Actions\NTALevelAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;
use App\Domain\Academic\Models\SemesterRemark;

class GPAClassificationController extends Controller
{
    /**
     * Display a list of levels
     */
    public function index(Request $request)
    {
    	$data = [
           'nta_levels'=>NTALevel::with(['award','programs'])->get(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
		   'classifications'=>GPAClassification::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('nta_level_id',$request->get('nta_level_id'))->paginate(20),
		   'nta_level'=>NTALevel::find($request->get('nta_level_id')),
		   'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')),
           'staff'=>User::find(Auth::user()->id)->staff,
		   'request'=>$request
    	];
    	return view('dashboard.settings.gpa-classifications',$data)->withTitle('NTA Levels');
    }

    /**
     * Store level into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
			'min_gpa'=>'required',
			'max_gpa'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        foreach($request->get('nta_level_id') as $id){
        $class = new GPAClassification;
		$class->nta_level_id = $id;
		$class->study_academic_year_id = $request->get('study_academic_year_id');
		$class->min_gpa = $request->get('min_gpa');
		$class->max_gpa = $request->get('max_gpa');
		$class->name = $request->get('name');
		$class->save();
		}

        return Util::requestResponse($request,'NTA Level created successfully');
    }

    /**
     * Update specified level
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
			'min_gpa'=>'required',
			'max_gpa'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(SemesterRemark::where('study_academic_year_id',$request->get('study_academic_year_id'))->first()){
            return redirect()->back()->with('error','Cannot be changed, the policy has already been used');
        }

        $class = GPAClassification::find($request->get('gpa_classfication_id'));
		$class->nta_level_id = $request->get('nta_level_id');
		$class->study_academic_year_id = $request->get('study_academic_year_id');
		$class->min_gpa = $request->get('min_gpa');
		$class->max_gpa = $request->get('max_gpa');
		$class->name = $request->get('name');
		$class->save();

        return Util::requestResponse($request,'GPA classification updated successfully');
    }

    /**
     * Remove the specified level
     */
    public function destroy(Request $request, $id)
    {
        try{
            $class = GPAClassification::findOrFail($id);
            if(SemesterRemark::where('study_academic_year_id',$class->study_academic_year_id)->first()){
                return redirect()->back()->with('error','Cannot be changed, the policy has already been used');
            }
            return 1;
            $class->delete();
            return redirect()->back()->with('message','GPA classification deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
