<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Actions\GradingPolicyAction;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth, DB;
use App\Domain\Academic\Models\ExaminationResult;

class GradingPolicyController extends Controller
{
    /**
     * Display a list of policies
     */
    public function index(Request $request)
    {
        if($request->has('nta_level')){
            $policies = GradingPolicy::with('ntaLevel')->where('study_academic_year_id',$request->get('study_academic_year_id'))->orderBy('nta_level_id',$request->get('nta_level'))->paginate(20);
        }else{
            $policies = GradingPolicy::with('ntaLevel')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
        }
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'policies'=>$policies,
           'nta_levels'=>NTALevel::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.grading-policies',$data)->withTitle('Grading Policies');
    }

    /**
     * Assign previous policies
     */
    public function assignPreviousPolicies(Request $request, $ac_year_id)
    {
         DB::beginTransaction();
         $academic_year = StudyAcademicYear::latest()->take(1)->skip(1)->first();
         if(!$academic_year){
         	DB::rollback();
             return redirect()->back()->with('error','No previous study academic year');
         }
         $policies = GradingPolicy::whereHas('studyAcademicYear',function($query) use ($academic_year){
                  $query->where('id',$academic_year->id);
         })->get();

         if(count($policies) == 0){
             DB::rollback();
             return redirect()->back()->with('error','No previous grading policies');
         }
         
         foreach ($policies as $key => $policy){
            $system = new GradingPolicy;
            $system->min_score = $policy->min_score;
            $system->max_score = $policy->max_score;
            $system->grade = $policy->grade;
            $system->point = $policy->point;
            $system->remark = $policy->remark;
            $system->nta_level_id = $policy->nta_level_id;
            $system->study_academic_year_id = $ac_year_id;
            $system->save();
         }
         DB::commit();

         return redirect()->back()->with('message','Grading policy assignment completed successfully');
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

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('grade',$request->get('grade'))->count() != 0){
        	return redirect()->back()->with('error','Grading policy already added for this NTA level');
        }

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))
                        ->where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('min_score','<=',$request->get('min_score'))
                        ->where('max_score','>=',$request->get('min_score'))
                        ->where('point',$request->get('point'))
                        ->count() != 0){
        	return redirect()->back()->with('error','Grading policy overlapping for this NTA level');
        }

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))
                        ->where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('min_score','<=',$request->get('max_score'))
                        ->where('max_score','>=',$request->get('max_score'))
                        ->where('point',$request->get('point'))
                        ->count() != 0){
        	return redirect()->back()->with('error','Grading policy overlapping for this NTA level');
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

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))
                        ->where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('min_score','<=',$request->get('min_score'))
                        ->where('max_score','>=',$request->get('min_score'))
                        ->where('point',$request->get('point'))
                        ->count() != 0){
        	return redirect()->back()->with('error','Grading policy overlapping for this NTA level');
        }

        if(GradingPolicy::where('nta_level_id',$request->get('nta_level_id'))
                        ->where('study_academic_year_id',$request->get('study_academic_year_id'))
                        ->where('min_score','<=',$request->get('max_score'))
                        ->where('max_score','>=',$request->get('max_score'))
                        ->where('point',$request->get('point'))
                        ->count() != 0){
        	return redirect()->back()->with('error','Grading policy overlapping for this NTA level');
        }

        if(ExaminationResult::whereHas('moduleAssignment',function($query) use($request){$query->where('study_academic_year_id',$request->get('study_academic_year_id'));})->first()){
            return redirect()->back()->with('error','Cannot be changed, the policy has already been used');
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
            if(ExaminationResult::whereHas('moduleAssignment',function($query) use($policy){$query->where('study_academic_year_id',$policy->study_academic_year_id);})->first()){
                return redirect()->back()->with('error','Cannot be changed, the policy has already been used');
            }
            return 1;
            $policy->delete();
            return redirect()->back()->with('message','Grading policy deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
