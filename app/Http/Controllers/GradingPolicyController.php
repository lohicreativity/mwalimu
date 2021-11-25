<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Actions\GradingPolicyAction;
use App\Utils\Util;
use Validator;

class GradingPolicyController extends Controller
{
    /**
     * Display a list of policies
     */
    public function index(Request $request)
    {
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'policies'=>GradingPolicy::with('ntaLevel')->where('study_academic_year_id',$request->get('study_academic_year_id'))->paginate(20),
           'nta_levels'=>NTALevel::all()
    	];
    	return view('dashboard.academic.grading-policies',$data)->withTitle('Grading Policies');
    }

    /**
     * Store policy into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'min_score'=>'required',
            'max_score'=>'required',
            'grade'=>'required',
            'point'=>'required',
            'remark'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))->where('grade',$request->get('grade'))->count() != 0){
        	return redirect()->back()->with('error','Grading policy already added for this NTA level');
        }


        (new GradingPolicyAction)->store($request);

        return Util::requestResponse($request,'Grading policy created successfully');
    }

    /**
     * Update specified policy
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'min_score'=>'required',
            'max_score'=>'required',
            'grade'=>'required',
            'point'=>'required',
            'remark'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new GradingPolicyAction)->update($request);

        return Util::requestResponse($request,'Grading policy updated successfully');
    }

    /**
     * Remove the specified policy
     */
    public function destroy(Request $request, $id)
    {
        try{
            $policy = GradingPolicy::findOrFail($id);
            $policy->delete();
            return redirect()->back()->with('message','Grading policy deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
