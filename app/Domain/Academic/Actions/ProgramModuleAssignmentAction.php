<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\ProgramModuleAssignmentRequest;
use App\Domain\Academic\Repositories\Interfaces\ProgramModuleAssignmentInterface;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Domain\Academic\Models\CourseWorkComponent;
use App\Domain\Academic\Models\ExaminationResult;
use App\Models\User;
use App\Utils\Util;
use Auth;

class ProgramModuleAssignmentAction implements ProgramModuleAssignmentInterface{
	
	public function store(Request $request){
		$assignment = new ProgramModuleAssignment;
                $assignment->semester_id = $request->get('semester_id');
                $assignment->campus_program_id = $request->get('campus_program_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->year_of_study = $request->get('year_of_study');
                $assignment->category = $request->get('category');
                $assignment->type = $request->get('type');

                $campus = CampusProgram::find($request->get('campus_program_id'))->campus;
                $prog = ProgramModuleAssignment::where('module_id',$request->get('module_id'))->where('campus_program_id',$request->get('campus_program_id'))
											   ->where('year_of_study',$request->get('year_of_study'))
											   ->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('policy_assigned',1)
											   ->whereHas('campusProgram',function($query) use($campus){
                         $query->where('campus_id',$campus->id);
                     })->first();
                if($prog){
                      $assignment->course_work_min_mark = $prog->course_work_min_mark;
                      $assignment->course_work_percentage_pass = $prog->course_work_percentage_pass;
                      $assignment->course_work_pass_score = $prog->course_work_pass_score;
                      $assignment->final_min_mark = $prog->final_min_mark;
                      $assignment->final_percentage_pass = $prog->final_percentage_pass;
                      $assignment->final_pass_score = $prog->final_pass_score;
                      $assignment->module_pass_mark = $prog->module_pass_mark;
                }else{
                        $assignment->course_work_min_mark = $request->get('course_work_min_mark');
                        $assignment->course_work_percentage_pass = $request->get('course_work_percentage_pass');
                        $assignment->course_work_pass_score = $request->get('course_work_pass_score');
                        $assignment->final_min_mark = $request->get('final_min_mark');
                        $assignment->final_percentage_pass = $request->get('final_percentage_pass');
                        $assignment->final_pass_score = $request->get('final_pass_score');
                        $assignment->module_pass_mark = $request->get('module_pass_mark');
                }

                $module = Module::with('departments')->find($request->get('module_id'));
                $staff = User::find(Auth::user()->id)->staff;
                if(!Util::collectionContainsKey($module->departments,$staff->department_id)){
                  if($prog){
                    $assignment->policy_assigned = 1;
                  }else{
                    $assignment->policy_assigned = 0;
                  }
                $assignment->save();

                
                    $req = new ProgramModuleAssignmentRequest;
                    $req->staff_id = $staff->id;
                    $req->program_module_assignment_id = $assignment->id;
                    $req->is_ready = $prog? 1 : 0;
                    $req->save();

                    return redirect()->back()->with('info','Module assignment request sent successfully');
                }else{
                    $assignment->policy_assigned = 1;
                    $assignment->save();   
                }
                return redirect()->back()->with('message','Programme module assignment created successfully');
	}

	public function update(Request $request){
	        $assignment = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));
                $assignment->semester_id = $request->get('semester_id');
                $assignment->campus_program_id = $request->get('campus_program_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->year_of_study = $request->get('year_of_study');
                $assignment->category = $request->get('category');
                $assignment->type = $request->get('type');
                $course_work_min_mark_changed = false;
                if($assignment->course_work_min_mark != $request->get('course_work_min_mark')){
                      $course_work_min_mark_changed = true;
                }
                $assignment->course_work_min_mark = $request->get('course_work_min_mark');
                $assignment->course_work_percentage_pass = $request->get('course_work_percentage_pass');
                $assignment->course_work_pass_score = $request->get('course_work_pass_score');
                $assignment->final_min_mark = $request->get('final_min_mark');
                $assignment->final_percentage_pass = $request->get('final_percentage_pass');
                $assignment->final_pass_score = $request->get('final_pass_score');
                $assignment->module_pass_mark = $request->get('module_pass_mark');
                $assignment->policy_assigned = 1;
                $assignment->save();

                ProgramModuleAssignment::where('module_id',$request->get('module_id'))->where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->update([
                        'course_work_min_mark'=>$request->get('course_work_min_mark'),
                        'course_work_pass_score'=>$request->get('course_work_pass_score'),
                        'course_work_percentage_pass'=>$request->get('course_work_percentage_pass'),
                        'final_min_mark'=>$request->get('final_min_mark'),
                        'final_pass_score'=>$request->get('final_pass_score'),
                        'final_percentage_pass'=>$request->get('final_percentage_pass'),
                        'module_pass_mark'=>$request->get('module_pass_mark'),
                        'policy_assigned'=>1
                ]);

                ProgramModuleAssignmentRequest::whereHas('programModuleAssignment',function($query) use ($request){
                        $query->where('module_id',$request->get('module_id'))->where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'));
                })->update(['is_ready'=>1]);

                if($course_work_min_mark_changed){
                        AssessmentPlan::whereHas('moduleAssignment',function($query) use ($request){
                                $query->where('program_module_assignment_id',$request->get('program_module_assignment_id'));
                        })->delete();

                        CourseWorkComponent::whereHas('moduleAssignment',function($query) use ($request){
                                $query->where('program_module_assignment_id',$request->get('program_module_assignment_id'));
                        })->delete();

                        ExaminationResult::whereHas('moduleAssignment',function($query) use ($request){
                                $query->where('program_module_assignment_id',$request->get('program_module_assignment_id'));
                        })->update(['final_processed_at'=>null,'final_remark'=>null,'course_work_remark'=>null,'grade'=>null,'total_score'=>null,'final_score'=>null,'final_uploaded_at'=>null,'processed_at'=>null]);

                        ModuleAssignment::where('program_module_assignment_id',$request->get('program_module_assignment_id'))->update(['course_work_process_status'=>null,'final_upload_status'=>null]);

                        $module_assignment = ModuleAssignment::where('program_module_assignment_id',$request->get('program_module_assignment_id'))->first();

                        CourseWorkResult::where('module_assignment_id',$module_assignment->id)->delete();
                }
	}
}