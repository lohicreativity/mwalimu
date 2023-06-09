<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ExaminationResultChange;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Models\User;
use Auth, Validator;

class CourseWorkResultController extends Controller
{
    /**
     * Display form for editing cw components
     */
    public function edit(Request $request, $student_id, $mod_assign_id, $exam_id, $redirect_url = null)
    {
    	try{
            if(Auth::user()->hasRole('staff')){
              $module_assignment = ModuleAssignment::find($mod_assign_id);
              if($module_assignment->final_upload_status == 'UPLOADED'){
                  return redirect()->back()->with('error','Unable to edit coursework because final results already uploaded');
              }
            }
    		$assessment_plans = AssessmentPlan::where('module_assignment_id',$mod_assign_id)->get();
    		if(count($assessment_plans) == 0){
    			return redirect()->back()->with('error','No assessment plan defined for this module');
    		}
	        $data = [
	          'student'=>Student::findOrFail($student_id),
	          'assessment_plans'=>$assessment_plans,
	          'exam_result'=>ExaminationResult::findOrFail($exam_id),
	          'results'=>CourseWorkResult::where('student_id',$student_id)->where('module_assignment_id',$mod_assign_id)->get(),
	          'module_assignment'=>ModuleAssignment::with('assessmentPlans','module','programModuleAssignment.campusProgram.program')->findOrFail($mod_assign_id),
            'redirect_url'=>$redirect_url,
	          'staff'=>User::find(Auth::user()->id)->staff
	        ];
	        return view('dashboard.academic.edit-course-work-results',$data)->withTitle('Edit Course Work Results');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display mark editing
     */
    public function markEdit(Request $request)
    {
        $staff = User::find(Auth::user()->id)->staff;
        $data = [
            'module_assignments'=>ModuleAssignment::whereHas('programModuleAssignment',function($query){
                    $query->where('semester_id',session('active_semester_id'));
            })->with(['module'])->where('study_academic_year_id',session('active_academic_year_id'))->where('staff_id',$staff->id)->get(),
            'staff'=>$staff
        ];
        return view('dashboard.academic.student-mark-editing',$data)->withTitle('Marks Editing');
    }

    /**
     * Redirect mark editing
     */
    public function postMarkEdit(Request $request)
    {
        $student = Student::where('registration_number',$request->get('registration_number'))->first();
        if(!$student){
            return redirect()->back()->with('error','Student does not exist');
        }
        $mod_assign = ModuleAssignment::with('programModuleAssignment')->find($request->get('module_assignment_id'));
        if($mod_assign->programModuleAssignment->category == 'OPTIONAL'){
            if(ProgramModuleAssignment::find($mod_assign->programModuleAssignment->id)->optedStudents()->count() == 0){
                return redirect()->back()->with('error','This optional module does not have opted students');
            }
        }
        if($exam = ExaminationResult::where('student_id',$student->id)->where('module_assignment_id',$request->get('module_assignment_id'))->first()){
           $exam = $exam;
        }else{
           $exam = new ExaminationResult;
           $exam->module_assignment_id = $request->get('module_assignment_id');
           $exam->student_id = $student->id;
           $exam->uploaded_by_user_id = Auth::user()->id;
           $exam->save();
        }
      

        return $this->edit($request,$student->id,$mod_assign->id,$exam->id, url('academic/results/student-mark-editing'));
    }


    /**
     * Update course work results
     */
    public function update(Request $request)
    {   
        $validations = [];
        $messages = [];
        $assessment_plans = AssessmentPlan::where('module_assignment_id',$request->get('module_assignment_id'))->get();
        foreach($assessment_plans as $plan){
           if($request->has('plan_'.$plan->id.'_score')){
              $validations['plan_'.$plan->id.'_score'] = 'numeric|min:0|max:100';
              $messages['plan_'.$plan->id.'_score.numeric'] = $plan->name.' must be numeric';
           }
        }

        $validation = Validator::make($request->all(),$validations,$messages);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

      try{
        	$module_assignment = ModuleAssignment::with('assessmentPlans','module','programModuleAssignment.campusProgram.program')->findOrFail($request->get('module_assignment_id'));
          
          ModuleAssignment::where('id',$module_assignment->id)->update(['course_work_process_status'=>'PROCESSED','final_upload_status'=>'UPLOADED']);    

          $module = Module::with('ntaLevel')->find($module_assignment->module_id);
        	// $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();

        	$assessment_plans = AssessmentPlan::where('module_assignment_id',$request->get('module_assignment_id'))->get();
        	foreach($assessment_plans as $plan){
        		if($request->has('plan_'.$plan->id.'_score')){
              if($request->get('plan_'.$plan->id.'_score') < 0 || $request->get('plan_'.$plan->id.'_score') > 100){
                  return redirect()->back()->with('error','Invalid score entered');
              }
    	    		if($res = CourseWorkResult::where('student_id',$request->get('student_id'))->where('assessment_plan_id',$plan->id)->first()){
    	    			$result = $res;
                        $result->student_id = $request->get('student_id');
                        $score_before = $result->score;
                        $result->score = ($request->get('plan_'.$plan->id.'_score')*$plan->weight)/100;
                        $result->assessment_plan_id = $plan->id;
                        $result->module_assignment_id = $request->get('module_assignment_id');
                        $result->uploaded_by_user_id = Auth::user()->id;
                        $result->save();

                        $change = new ExaminationResultChange;
                        $change->resultable_id = $result->id;
                        $change->from_score = $score_before;
                        $change->to_score = $result->score;
                        $change->resultable_type = 'course_work_result';
                        $change->user_id = Auth::user()->id;
                        $change->save();
    	    		}else{
    	    			$result = new CourseWorkResult;
                        $result->student_id = $request->get('student_id');
                        $result->score = ($request->get('plan_'.$plan->id.'_score')*$plan->weight)/100;
                        $result->assessment_plan_id = $plan->id;
                        $result->module_assignment_id = $request->get('module_assignment_id');
                        $result->uploaded_by_user_id = Auth::user()->id;
                        $result->save();
    	    		}
    	    		

                    
        	  }
        	}
        	$course_work = CourseWorkResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->sum('score');


                    $course_work_count = CourseWorkResult::whereHas('assessmentPlan',function($query) use ($request){
                         $query->where('name','LIKE','%Test%');
                      })->where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->count();

                        if($result = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->where(function($query){
							$query->where('exam_type','FINAL')->orWhere('exam_type','APPEAL');
						})->first()){
                            $exam_result = $result;
                            $exam_result->module_assignment_id = $request->get('module_assignment_id');
                            $exam_result->student_id = $request->get('student_id');
                            $exam_result->course_work_score = $course_work_count < 2? null : $course_work;
                            if(is_null($course_work) || $course_work_count < 2){
                               $exam_result->course_work_remark = 'INCOMPLETE';
                            }else{
                               $exam_result->course_work_remark = $module_assignment->programModuleAssignment->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
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
                               $exam_result->course_work_remark = $module_assignment->programModuleAssignment->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
                            }
                            $exam_result->uploaded_by_user_id = Auth::user()->id;
                            $exam_result->processed_by_user_id = Auth::user()->id;
                            $exam_result->processed_at = now();
                            $exam_result->save();
                        }

                 if($request->get('redirect_url')){
                    return redirect()->to($request->get('redirect_url'))->with('message','Marks updated successfully');
                 }

                 return redirect()->to('academic/results/'.$request->get('student_id').'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$module_assignment->programModuleAssignment->semester_id);
        }catch(\Exception $e){
			return $e->getMessage();
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
