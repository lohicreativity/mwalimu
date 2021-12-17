<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ExaminationPolicy;

class OverallRemarkController extends Controller
{
    /**
     * Compute overall GPA
     */
    public function computeGPA(Request $request, $student_id)
    {
    	$student = Student::with(['campusProgram.program'])->find($student_id);
    	if($student->year_of_study == $student->campusProgram->program->min_duration){
    		$results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();
	    	$years = [];
	    	$years_of_studies = [];
	    	$academic_years = [];
	    	foreach($results as $key=>$result){
	    		if(!array_key_exists($result->moduleAssignment->programModuleAssignment->year_of_study, $years)){
	               $years[$result->moduleAssignment->programModuleAssignment->year_of_study] = [];  
	               $years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
	    		}
	            if(!in_array($result->moduleAssignment->studyAcademicYear->id, $years[$result->moduleAssignment->programModuleAssignment->year_of_study])){

	            	$years[$result->moduleAssignment->programModuleAssignment->year_of_study][] = $result->moduleAssignment->studyAcademicYear->id;
	            }
	    	}

	    	foreach($years as $key=>$year){
	    		foreach ($year as $yr) {
	    			$years_of_studies[$key][] = StudyAcademicYear::with('academicYear')->find($yr);
	    		}
	    	}

	    	$overall_results = [];
	    	$overall_credits = 0;

	    	$semesters = Semester::all();

    		for($yr_of_study = 1; $yr_of_study <= $student->year_of_study; $yr_of_study++){
                    
                    foreach ($semesters as $semester) {
                    	
                    
    			    $ac_yr_id = $years_of_studies[$yr_of_study][0];
    			
		            $campus_program = $student->campusProgram;
		            $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study){
		                      $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study);
		                     })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
		                    $query->where('program_id',$campus_program->program->id);
		                  })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

		             $annual_module_assignments = $module_assignments;
		        
		              $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study){
		                    $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study)->where('semester_id',$request->get('semester_id'));
		                  })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
		                $query->where('program_id',$campus_program->program->id);
		                  })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

		                  
		              if(count($module_assignments) == 0){
		                  DB::rollback();
		                  return redirect()->back()->with('error','No results to process');
		              }


		              foreach($module_assignments as $assign){
		                if($assign->course_work_process_status != 'PROCESSED'){
		                  DB::rollback();
		                  return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
		                }
		                if(ExaminationResult::where('final_uploaded_at',null)->where('module_assignment_id',$assign->id)->where('student_id',$student->id)->count() != 0){
		                  DB::rollback();
		                  return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
		                }
		              }

		        $student_buffer = [];
		        $annual_credit = 0;

		          foreach ($module_assignments as $assignment) {
		            $results = ExaminationResult::with(['retakeHistory.retakableResults'=>function($query){
		            	 $query->latest();
		            },'carryHistory.carrableResults'=>function($query){
		            	 $query->latest();
		            }])->where('module_assignment_id',$assignment->id)->where('student_id',$student->id)->get();
		            $policy = ExaminationPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->study_academic_year_id)->where('type',$assignment->programModuleAssignment->campusProgram->program->category)->first();
		            
		              if(!$policy){
		                 return redirect()->back()->with('error','Some programmes are missing examination policy');
		              }

		              if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

		                  $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
		              }else{
		                $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
		              }

		              if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
		                     $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
		                })->whereNotNull('final_uploaded_at')->distinct()->count('module_assignment_id') < count($core_programs)){
		                  
		                  $available_programs = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
		                     $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
		                    })->whereNotNull('final_uploaded_at')->where('student_id',$student->id)->distinct()->get(['module_assignment_id']);
		                  $available_program_ids = [];
		                  $missing_programs = [];
		                  foreach($available_programs as $pr){
		                      $available_program_ids[] = $pr->moduleAssignment->programModuleAssignment->id;
		                  }
		                  foreach($core_programs as $prog){
		                      if(!in_array($prog->id, $available_program_ids)){
		                         $missing_programs[] = $prog->module->code;
		                      }
		                  }
		                  
		                  DB::rollback();
		                  return redirect()->back()->with('error','Some modules as missing final marks ('.implode(',', $missing_programs).')');
		              }

		              $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester->id)->first();

		              if($elective_policy){
		                if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
		                       $query->where('campus_program_id',$campus_program->id)->where('category','OPTIONAL');
		                  })->whereNotNull('final_uploaded_at')->where('student_id',$student->id)->distinct()->count('module_assignment_id') != $elective_policy->number_of_options){
		                    DB::rollback();
		                    return redirect()->back()->with('error','Some optional modules as missing final marks');
		                }
		              }
		            $total_credit = 0;
		            
		            foreach($core_programs as $prog){
		              if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){          
		                $annual_credit += $prog->module->credit;
		                }

		              if($prog->semester_id == $request->get('semester_id')){
		                 $total_credit += $prog->module->credit;
		                }
		            }

		            foreach($results as $key=>$result){          
		              
		                    $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
		                      $query->where('id',$student->id);
		                        })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();

		                   foreach($optional_programs as $prog){
		                       $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
		                       $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;
		                   }
                              
                            if($result->retakeHistory && isset($result->retakeHistory->retakeHistory->retakableResults[0])){
		                        $processed_result = ExaminationResult::find($result->retakeHistory->retakeHistory->retakableResults[0]->id);
		                    }elseif($result->carryHistory && isset($result->carryHistory->carrableResults[0])){
                                $processed_result = ExaminationResult::find($result->carryHistory->carrableResults[0]->id);
		                    }else{
                                $processed_result = ExaminationResult::find($result->id);
		                    }
		                    if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
		                        $processed_result->total_score = null;
		                    }else{
		                      $processed_result->total_score = round($result->course_work_score + $result->final_score);
		                    }

		                    $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->total_score))->where('max_score','>=',round($processed_result->total_score))->first();
		      
		                    if(!$grading_policy){
		                       DB::rollback();
		                       return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
		                    }
		                    
		                    if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
		                      $processed_result->grade = null;
		                        $processed_result->point = null;
		                        $processed_result->final_exam_remark = $result->final_remark;
		                    }else{
		                      $processed_result->grade = $grading_policy? $grading_policy->grade : null;
		                        $processed_result->point = $grading_policy? $grading_policy->point : null;
		                        if($processed_result->course_work_remark == 'FAIL' || $processed_result->final_remark == 'FAIL'){
		                           $processed_result->final_exam_remark = 'FAIL';
		                           $processed_result->grade = 'F';
		                           $processed_result->point = 0;
		                        }else{
		                          $processed_result->final_exam_remark = $policy->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';
		                        }

		                        if($processed_result->supp_score){
		                          if(Util::stripSpacesUpper($assignment->module->ntaLevel->name) == Util::stripSpacesUpper('NTA Level 7')){
		                                $processed_result->final_exam_remark = $policy->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'CARRY';
		                          }else{
		                                $processed_result->final_exam_remark = $policy->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
		                          }

		                          $processed_result->supp_processed_at = now();
		                          $processed_result->supp_processed_by_user_id = Auth::user()->id;
		                          
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

		                    $student_buffer[$student->id]['results'][] =  $processed_result;
		                    $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
		                    $student_buffer[$student->id]['total_credit'] = $total_credit;

		                    if($processed_result->final_exam_remark == 'RETAKE'){
		                            if($hist = RetakeHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
		                              $history = $hist;
		                            }else{
		                              $history = new RetakeHistory;
		                            }

		                            $history->student_id = $student->id;
		                            $history->study_academic_year_id = $ac_yr_id;
		                            $history->module_assignment_id = $assignment->id;
		                            $history->examination_result_id = $processed_result->id;
		                            $history->save();
		                          }

		                          if($processed_result->final_exam_remark == 'CARRY'){
		                            if($hist = CarryHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
		                              $history = $hist;
		                            }else{
		                              $history = new CarryHistory;
		                            }

		                            $history->student_id = $student->id;
		                            $history->study_academic_year_id = $ac_yr_id;
		                            $history->module_assignment_id = $assignment->id;
		                            $history->examination_result_id = $processed_result->id;
		                            $history->save();
		                          }

		            }
		          }
		          
		          foreach ($annual_module_assignments as $assign) {
		          $annual_results = ExaminationResult::where('module_assignment_id',$assign->id)->where('student_id',$student->id)->get();

		            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

		              $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
		            }else{
		              $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
		            }
		      
		          $annual_credit = 0;
		          foreach($core_programs as $prog){            
		                $annual_credit += $prog->module->credit;
		                $overall_credits += $prog->module->credit;
		          }
		            
		          foreach($annual_results as $key=>$result){
		                
		                $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
		                  $query->where('id',$student->id);
		                    })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
		               
		               $student_buffer[$student->id]['annual_results'][] =  $result;
		               $overall_results[] = $result;
		               $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
		               $student_buffer[$student->id]['annual_credit'] = $annual_credit;
		               foreach($optional_programs as $prog){
		                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
		                   $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
		                   $overall_credits += $student_buffer[$student->id]['opt_credit'];
		               }
		            }
		        }

		          foreach($student_buffer as $key=>$buffer){
		               $pass_status = 'PASS';
		               $supp_exams = [];
		               $retake_exams = [];
		               $carry_exams = [];
		               foreach($buffer['results'] as $res){
		                  if($res->final_exam_remark == 'INCOMPLETE'){
		                      $pass_status = 'INCOMPLETE';
		                      break;
		                  }

		                  if($res->final_exam_remark == 'POSTPONED'){
		                      $pass_status = 'POSTPONED';
		                      break;
		                  }

		                  if($res->final_exam_remark == 'RETAKE'){
		                      $pass_status = 'RETAKE'; 
		                      $retake_exams[] = $res->moduleAssignment->module->code;
		                      break;
		                  }  

		                  if($res->final_exam_remark == 'CARRY'){
		                      $pass_status = 'CARRY'; 
		                      $carry_exams[] = $res->moduleAssignment->module->code;
		                      break;
		                  } 

		                  if($res->final_exam_remark == 'FAIL'){
		                      $pass_status = 'SUPP'; 
		                      $supp_exams[] = $res->moduleAssignment->module->code;
		                  }       
		                }
		               
		               if($rem = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$request->get('semester_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
		                  $remark = $rem;  
		               }else{
		                  $remark = new SemesterRemark;
		               }
		                $remark->study_academic_year_id = $ac_yr_id;
		                $remark->student_id = $key;
		                $remark->semester_id = $request->get('semester_id');
		                $remark->remark = $pass_status;
		                if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED'){
		                     $remark->gpa = null;
		                }else{
		                   $remark->gpa = Util::computeGPA($buffer['total_credit'],$buffer['results']);
		                }
		                $remark->year_of_study = $buffer['year_of_study'];
		                $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
		                $remark->save();
		               
		               
		                 $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();
		                
		                    
		                   if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->first()){
		                        $rem = $rm;
		                      
		                    }else{
		                        $rem = new AnnualRemark;
		                    }
		                    $rem->student_id = $key;
		                    $rem->year_of_study = $buffer['year_of_study'];
		                    $rem->study_academic_year_id = $ac_yr_id;
		                    $rem->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
		                    if($rem->remark == 'INCOMPLETE' || $rem->remark == 'INCOMPLETE' || $rem->remark == 'POSTPONED'){
		                       $rem->gpa = null;
		                    }else{
		                         $rem->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
		                    }
		                    $rem->save();
		               }


    		    }
            }

    		$annual_remarks = AnnualRemark::where('student_id',$student->id)->get();
		                
		                    
           if($rm = OverallRemark::where('student_id',$student->id)->first()){
                $rem = $rm;
              
            }else{
                $rem = new OverallRemark;
            }
            $rem->student_id = $student->id;
            $rem->remark = Util::getOverallRemark($annual_remarks,$overall_results);
            if($rem->remark == 'INCOMPLETE' || $rem->remark == 'INCOMPLETE' || $rem->remark == 'POSTPONED'){
               $rem->gpa = null;
            }else{
                 $rem->gpa = Util::computeGPA($overall_credits,$overall_results);
            }
            $rem->save();
    	}
    }
}
