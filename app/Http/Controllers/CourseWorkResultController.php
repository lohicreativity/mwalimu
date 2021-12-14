<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Registration\Models\Student;
use Auth;

class CourseWorkResultController extends Controller
{
    /**
     * Display form for editing cw components
     */
    public function edit(Request $request, $student_id, $mod_assign_id)
    {
    	try{
	        $data = [
	          'students'=>Student::findOrFail($student_id),
	          'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$mod_assign_id)->get()
	        ];
	        return view('dashboard.academic.edit-course-work-results',$data)->withTitle('Edit Course Work Results');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Update course work results
     */
    public function update(Request $request)
    {
        try{
    	$module_assignment = ModuleAssignment::with('assessmentPlans','module','programModuleAssignment.campusProgram.program')->findOrFail($request->get('module_assignment_id'));
              

        $module = Module::with('ntaLevel')->find($module_assignment->module_id);
    	$policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
	    if(!$policy){
	        return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
	    }

    	$assessment_plans = AssessmentPlan::where('module_assignment_id',$request->get('module_assignment_id'))->get();
    	foreach($assessment_plans as $plan){
    		if($request->has('plan_'.$plan->id.'_score')){
	    		if($re = CourseWorkResult::where('student_id',$request->get('student_id'))->where('assessment_plan_id','plan_'.$plan->id)->first()){
	    			$result = $res;
	    		}else{
	    			$result = new CourseWorkResult;
	    		}
	    		$result->student_id = $request->get('student_id');
	    		$result->score = $request->get('plan_'.$plan->id.'_score');
	    		$result->module_assignment_id = $request->get('module_assignment_id');
	    		$result->uploaded_by_user_id = Auth::user()->id;
	    		$result->save();
    	    }
    	}
    	$course_work = CourseWorkResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->sum('score');
                $course_work_count = CourseWorkResult::whereHas('assessmentPlan',function($query) use ($request){
                     $query->where('name','LIKE','%Test%');
                  })->where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->count();

                    if($result = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->where('exam_type','FINAL')->first()){
                        $exam_result = $result;
                        $exam_result->module_assignment_id = $request->get('module_assignment_id');
                        $exam_result->student_id = $request->get('student_id');
                        $exam_result->course_work_score = $course_work_count < 2? null : $course_work;
                        if(is_null($course_work) || $course_work_count < 2){
                           $exam_result->course_work_remark = 'INCOMPLETE';
                        }else{
                           $exam_result->course_work_remark = $policy->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
                        }
                        
                        $exam_result->processed_by_user_id = Auth::user()->id;
                        $exam_result->processed_at = now();
                        $exam_result->save();
                    }else{
                        $exam_result = new ExaminationResult;
                        $exam_result->module_assignment_id = $request->get('module_assignment_id');
                        $exam_result->student_id = $request->get('student_id');
                        $exam_result->course_work_score = $course_work_count < 2? null : $course_work;
                        if(is_null($course_work) || $course_work_count < 2){
                           $exam_result->course_work_remark = 'INCOMPLETE';
                        }else{
                           $exam_result->course_work_remark = $policy->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
                        }
                        $exam_result->uploaded_by_user_id = Auth::user()->id;
                        $exam_result->processed_by_user_id = Auth::user()->id;
                        $exam_result->processed_at = now();
                        $exam_result->save();
                    }
                    
             }
             return redirect()->to('academic/results/'.$request->get('student_id').'/'.$request->get('study_academic_year_id').'/process-student-results');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
