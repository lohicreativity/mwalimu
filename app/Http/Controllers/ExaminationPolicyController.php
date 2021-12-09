<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Actions\ExaminationPolicyAction;
use App\Utils\Util;
use Validator, DB;

class ExaminationPolicyController extends Controller
{
    /**
     * Display a list of examinations
     */
    public function index(Request $request)
    {
    	$data = [
    	   'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'examination_policies'=>ExaminationPolicy::with(['studyAcademicYear.academicYear','ntaLevel'])->paginate(20),
           'nta_levels'=>NTALevel::all()
    	];
    	return view('dashboard.academic.examination-policies',$data)->withTitle('Examination Policies');
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
         $policies = ExaminationPolicy::whereHas('studyAcademicYear',function($query) use ($academic_year){
                  $query->where('id',$academic_year->id);
         })->get();

         if(count($policies) == 0){
             DB::rollback();
             return redirect()->back()->with('error','No previous examination policies');
         }
         
         foreach ($policies as $key => $policy){
            $system = new ExaminationPolicy;
            $system->course_work_min_mark = $policy->course_work_min_mark;
            $system->course_work_percentage_pass = $policy->course_work_percentage_pass;
            $system->course_work_pass_score = $policy->course_work_pass_score;
            $system->final_min_mark = $policy->final_min_mark;
            $system->final_percentage_pass = $policy->final_percentage_pass;
            $system->final_pass_score = $policy->final_pass_score;
            $system->module_pass_mark = $policy->module_pass_mark;
            $system->type = $policy->type;
            $system->nta_level_id = $policy->nta_level_id;
            $system->study_academic_year_id = $ac_year_id;
            $system->save();
         }
         DB::commit();


         return redirect()->back()->with('message','Examination policy assignment completed successfully');
    }

    /**
     * Store examination into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'module_pass_mark'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        if(ExaminationPolicy::where('type',$request->get('type'))->where('nta_level_id',$request->get('nta_level_id'))->count() != 0){
        	return redirect()->back()->with('error','Examination policy for this NTA level already exists');
        }


        (new ExaminationPolicyAction)->store($request);

        return Util::requestResponse($request,'Examination policy created successfully');
    }

    /**
     * Update specified examination
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'module_pass_mark'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }

        (new ExaminationPolicyAction)->update($request);

        return Util::requestResponse($request,'Examination policy updated successfully');
    }

    /**
     * Remove the specified examination
     */
    public function destroy(Request $request, $id)
    {
        try{
            $policy = ExaminationPolicy::findOrFail($id);
            $policy->delete();
            return redirect()->back()->with('message','Examination policy deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }

    }

}