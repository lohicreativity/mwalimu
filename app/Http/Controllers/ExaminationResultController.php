<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Utils\Util;
use Auth;

class ExaminationResultController extends Controller
{
    /**
     * Display form for processing results
     */
    public function showProcess(Request $request)
    {
    	$data = [
    	    'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'campus_programs'=>$request->has('campus_id')? CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get() : [],
            'campus'=>Campus::find($request->get('campus_id')),
            'semesters'=>Semester::all(),
            'campuses'=>Campus::all(),
    	];
    	return view('dashboard.academic.results-processing',$data)->withTitle('Results Processing');
    }

    /**
     * Process results
     */
    public function process(Request $request)
    {
    	$policy_status = true;
    	$grading_policy_status = true;
    	$campus_program = CampusProgram::with('program')->find(explode('_',$request->get('campus_program_id'))[0]);
    	if($request->get('semester_id') != 'SUPPLEMENTARY'){
	    	$module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
	                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
	    	        })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$campus_program->program->id);
	    	        })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
        }else{
        	$module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
    	         })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$campus_program->program->id);
	    	    })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
        }
    	foreach ($module_assignments as $assignment) {
    		$results = ExaminationResult::where('module_assignment_id',$assignment->id)->get();
    		$policy = ExaminationPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->study_academic_year_id)->where('type',$assignment->programModuleAssignment->campusProgram->program->category)->first();
    		
    		if(!$policy){
    			$policy_status = false;
    			return redirect()->back()->with('error','Some programmes are missing examination policy');
    		}

    		foreach($results as $key=>$result){
               
                $processed_result = ExaminationResult::find($result->id);
                if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
                    $processed_result->total_score = null;
                }else{
                	$processed_result->total_score = $result->course_work_score + $result->final_score;
                }

                $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($result->total_score))->where('max_score','>=',round($result->total_score))->first();
  
                if(!$grading_policy){
                   $grading_policy_status = false;
                   return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
                }
                
                if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
                	$processed_result->grade = null;
                    $processed_result->point = null;
                    $processed_result->final_exam_remark = $result->final_remark;
                }else{
                	$processed_result->grade = $grading_policy? $grading_policy->grade : null;
                    $processed_result->point = $grading_policy? $grading_policy->point : null;
                    if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL'){
                       $processed_result->final_exam_remark = 'FAIL';
                    }else{
                      $processed_result->final_exam_remark = $policy->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';
                    }
                }

                if($result->exam_type == 'SUPP'){
                   $processed_result->total_score = $result->final_score;
                   $processed_result->grade = 'C';
                }
                
                if($result->exam_category == 'CARRY'){
                	 $processed_result->course_work_score = null;
                	 $processed_result->course_work_remark = null;
                }

                
                $processed_result->final_processed_by_user_id = Auth::user()->id;
                $processed_result->final_processed_at = now();
                $processed_result->save();

               if(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 1')){

               }elseif(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 2')){

               }elseif($request->get('semester_id') == 'SUPPLEMENTARY'){

               }

    		}
    	}

    	// if(!$policy_status){
    	// 	return redirect()->back()->with('error','Some programmes are missing examination policy');
    	// }

    	// if(!$grading_policy_status){
    	// 	return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
    	// }
        
        // Determine semester, annual and overall remarks
    	if(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 1')){


        }elseif(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 2')){

        }elseif($request->get('semester_id') == 'SUPPLEMENTARY'){

        }

        return redirect()->back()->with('message','Results processed successfully');
    }
}
