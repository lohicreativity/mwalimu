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
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\OverallRemark;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\ExaminationProcessRecord;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Models\User;
use App\Utils\Util;
use Auth, DB;

class ExaminationResultController extends Controller
{
    /**
     * Display form for processing results
     */
    public function showProcess(Request $request)
    {
      $first_semester_publish_status = false;
      if(ResultPublication::whereHas('semester',function($query){
           $query->where('name','LIKE','%1%');
         })->where('status','PUBLISHED')->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
         $first_semester_publish_status = true;
      }
      $second_semester_publish_status = false;
      if(ResultPublication::whereHas('semester',function($query){
           $query->where('name','LIKE','%2%');
         })->where('status','PUBLISHED')->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
         $second_semester_publish_status = true;
      }
    	$data = [
    	    'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'campus_programs'=>$request->has('campus_id')? CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get() : [],
            'campus'=>Campus::find($request->get('campus_id')),
            'semesters'=>Semester::all(),
            'campuses'=>Campus::all(),
            'active_semester'=>Semester::where('status','ACTIVE')->first(),
            'first_semester_publish_status'=>$first_semester_publish_status,
            'second_semester_publish_status'=>$second_semester_publish_status,
            'publications'=>$request->has('study_academic_year_id')? ResultPublication::with(['studyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get() : [],
            'process_records'=>ExaminationProcessRecord::whereHas('campusProgram',function($query) use ($request){
                  $query->where('campus_id',$request->get('campus_id'));
               })->with(['campusProgram.program','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->paginate(20),
            'staff'=>User::find(Auth::user()->id)->staff,
            'request'=>$request
    	];
    	return view('dashboard.academic.results-processing',$data)->withTitle('Results Processing');
    }

    /**
     * Process results
     */
    public function process(Request $request)
    {
    	DB::beginTransaction();
    	$campus_program = CampusProgram::with('program')->find(explode('_',$request->get('campus_program_id'))[0]);
    	$module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
    	         })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$campus_program->program->id);
	    	    })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
    	 $annual_module_assignments = $module_assignments;

    	if($request->get('semester_id') != 'SUPPLEMENTARY'){
    		$semester = Semester::find($request->get('semester_id'));
    
    	    $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
    	        })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
    	    	$query->where('program_id',$campus_program->program->id);
    	        })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();
         }
        	
      if(count($module_assignments) == 0){
          DB::rollback();
          return redirect()->back()->with('error','No results to process');
      }


        foreach($module_assignments as $assign){
        	if($assign->course_work_process_status != 'PROCESSED'){
            DB::rollback();
        		return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
        	}
        	if(ExaminationResult::where('final_uploaded_at',null)->where('module_assignment_id',$assign->id)->count() != 0){
            DB::rollback();
        		return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
        	}
        }

        $student_buffer = [];
        foreach ($annual_module_assignments as $assign) {
        	$annual_results = ExaminationResult::where('module_assignment_id',$assign->id)->get();
        	if($request->get('semester_id') != 'SUPPLEMENTARY'){
	        	if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

	    		    $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
  	    		}else{
  	    			$core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
  	    		}
    	    }else{
    	    	$core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
    	    }

            $annual_credit = 0;
    		foreach($core_programs as $prog){
    			if($request->get('semester_id') != 'SUPPLEMENTARY'){
	    			  if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){    			
		    			   $annual_credit += $prog->module->credit;
	    	      }
    	     }else{
    	        	  $annual_credit += $prog->module->credit;
    	     }
    		}
            
        	foreach($annual_results as $key=>$result){
    			$student = Student::find($result->student_id);
                
                $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	        $query->where('id',$student->id);
                    })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
               
               $student_buffer[$student->id]['annual_results'][] =  $result;
               $student_buffer[$student->id]['year_of_study'] = $student->year_of_study;
               $student_buffer[$student->id]['annual_credit'] = $annual_credit;
               foreach($optional_programs as $prog){
                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                   $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
               }
            }
        }

    	foreach ($module_assignments as $assignment) {
    		$results = ExaminationResult::where('module_assignment_id',$assignment->id)->get();
    		$policy = ExaminationPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->study_academic_year_id)->where('type',$assignment->programModuleAssignment->campusProgram->program->category)->first();
    		
    		if(!$policy){
    			return redirect()->back()->with('error','Some programmes are missing examination policy');
    		}
    		if($request->get('semester_id') != 'SUPPLEMENTARY'){
	            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

	    		    $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
	    		}else{
	    			$core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assignment->programModuleAssignment->campus_program_id)->get();
	    		}
    	    }else{
    	    	$core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
    	    }

          if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                 $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
            })->whereNotNull('final_uploaded_at')->distinct()->count('module_assignment_id') < count($core_programs)){
              
              $available_programs = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                 $query->where('campus_program_id',$campus_program->id)->where('category','COMPULSORY');
                })->whereNotNull('final_uploaded_at')->distinct()->get(['module_assignment_id']);
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

          $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->first();

          if($elective_policy){
            if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                   $query->where('campus_program_id',$campus_program->id)->where('category','OPTIONAL');
              })->whereNotNull('final_uploaded_at')->distinct()->count('module_assignment_id') != $elective_policy->number_of_options){
                DB::rollback();
                return redirect()->back()->with('error','Some optional modules as missing final marks');
            }
          }
    		$total_credit = 0;
    		
    		if($request->get('semester_id') != 'SUPPLEMENTARY'){
                 $semester = Semester::find($request->get('semester_id'));
            }
    		foreach($core_programs as $prog){
    			if($request->get('semester_id') != 'SUPPLEMENTARY'){
    			if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){    			
	    			$annual_credit += $prog->module->credit;
    	     	}
    	        }else{
    	        	$annual_credit += $prog->module->credit;
    	        }

	     		   if($prog->semester_id == $request->get('semester_id')){
    			      $total_credit += $prog->module->credit;
    		      }
    		  }

    		  foreach($results as $key=>$result){
    			$student = Student::find($result->student_id);
                
    			
                $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
         	        $query->where('id',$student->id);
                    })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
               
               $student_buffer[$student->id]['results'][] =  $result;
               $student_buffer[$student->id]['year_of_study'] = $student->year_of_study;
               $student_buffer[$student->id]['total_credit'] = $total_credit;

               foreach($optional_programs as $prog){
                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                   $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;
               }

                $processed_result = ExaminationResult::find($result->id);
                if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
                    $processed_result->total_score = null;
                }else{
                	$processed_result->total_score = round($result->course_work_score + $result->final_score);
                }

                $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->total_score))->where('max_score','>=',round($processed_result->total_score))->first();
  
                if(!$grading_policy){
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

                if($processed_result->final_exam_remark == 'RETAKE'){
                    		if($hist = RetakeHistory::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
                    			$history = $hist;
                    		}else{
                    			$history = new RetakeHistory;
                    		}

                    		$history->student_id = $student->id;
                    		$history->study_academic_year_id = $request->get('study_academic_year_id');
                    		$history->module_assignment_id = $assignment->id;
                    		$history->examination_result_id = $processed_result->id;
                    		$history->save();
                    	}

                    	if($processed_result->final_exam_remark == 'CARRY'){
                    		if($hist = CarryHistory::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->where('module_assignment_id',$assignment->id)->first()){
                    			$history = $hist;
                    		}else{
                    			$history = new CarryHistory;
                    		}

                    		$history->student_id = $student->id;
                    		$history->study_academic_year_id = $request->get('study_academic_year_id');
                    		$history->module_assignment_id = $assignment->id;
                    		$history->examination_result_id = $processed_result->id;
                    		$history->save();
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
               

               if($request->get('semester_id') != 'SUPPLEMENTARY'){
                   if($rem = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
                   	  $remark = $rem;  
               	   }else{
               	   	  $remark = new SemesterRemark;
               	   }
               	      $remark->study_academic_year_id = $request->get('study_academic_year_id');
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

               }
               
               if($request->get('semester_id') == 'SUPPLEMENTARY'){
                   $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->get();
	               	  
	                   if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
	                      $remark = $rm;
	                      $remark->student_id = $key;
	                      $remark->year_of_study = $buffer['year_of_study'];
	                   	  $remark->study_academic_year_id = $request->get('study_academic_year_id');
	                   	  $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
	                   	  if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED'){
	                   	     $remark->gpa = null;
	                   	  }else{
                             $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
	                   	  } 
	                   	  $remark->save();
	                   }
               }else{
               	   $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('year_of_study',$buffer['year_of_study'])->get();
	               if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
	               	  
	                   if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
	                      $remark = $rm;
	                      
	                   }else{
	                   	  $remark = new AnnualRemark;
	                   }
	                      $remark->student_id = $key;
	                      $remark->year_of_study = $buffer['year_of_study'];
	                   	  $remark->study_academic_year_id = $request->get('study_academic_year_id');
	                   	  $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
	                   	  if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED'){
	                   	     $remark->gpa = null;
	                   	  }else{
                             $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
	                   	  }
	                   	  $remark->save();
	               }
               }
               if($pub = ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->first()){
               	  $publication = $pub;
               }else{
               	  $publication = new ResultPublication;
               	  $publication->study_academic_year_id = $request->get('study_academic_year_id');
               	  $publication->semester_id = $request->get('semester_id') == 'SUPPLEMENTARY'? 0 : $request->get('semester_id');
               	  $publication->type = $request->get('semester_id') == 'SUPPLEMENTARY'? 'SUPP' : 'FINAL';
               	  $publication->published_by_user_id = Auth::user()->id;
               	  $publication->save();
               }

    		}

        $process = new ExaminationProcessRecord;
        $process->study_academic_year_id = $request->get('study_academic_year_id');
        $process->semester_id = $request->get('semester_id') == 'SUPPLEMENTARY'? 0 : $request->get('semester_id');
        $process->year_of_study = explode('_',$request->get('campus_program_id'))[2];
        $process->campus_program_id = explode('_',$request->get('campus_program_id'))[0];
        $process->save();
    		DB::commit();

        return redirect()->back()->with('message','Results processed successfully');
    }

    /**
     * Display form for adding results
     */
    public function create(Request $request, $student_id,$ac_yr_id,$yr_of_study)
    {
        try{
            $student = Student::findOrFail($student_id);
            $results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();
              $programs = [];
              foreach($results as $key=>$result){
                if(!array_key_exists($result->moduleAssignment->programModuleAssignment->id, $programs)){
                       $programs[$result->moduleAssignment->programModuleAssignment->id] = $result->moduleAssignment->programModuleAssignment;  
                }        
              }

            $data = [
               'core_programs'=>ProgramModuleAssignment::where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->whereNotIn('id',array_keys($programs))->get(),
               'opt_programs'=>ProgramModuleAssignment::whereHas('students',function($query) use($student){
                     $query->where('id',$student->id);
                 })->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->whereNotIn('id',array_keys($programs))->get()
            ];
            return view('dashboard.academic.add-examination-results',$data)->withTitle('Add Examination Results');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display form for editing results
     */
    public function edit(Request $request, $student_id,$ac_yr_id,$prog_id)
    {
        try{
            $student = Student::findOrFail($student_id);
            $result = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($prog_id,$ac_yr_id){
                    $query->where('id',$prog_id)->where('study_academic_year_id',$ac_yr_id);
                 })->with(['moduleAssignment.programModuleAssignment.module.ntaLevel','moduleAssignment.programModuleAssignment.campusProgram.program'])->where('student_id',$student->id)->firstOrFail();
            $policy = ExaminationPolicy::where('nta_level_id',$result->moduleAssignment->programModuleAssignment->module->ntaLevel->id)->where('study_academic_year_id',$result->moduleAssignment->study_academic_year_id)->where('type',$result->moduleAssignment->programModuleAssignment->campusProgram->program->category)->first();
            if(!$policy){
               return redirect()->back()->with('error','No examination policy defined for this NTA level this academic year');
            }
            $data = [
               'result'=>$result,
               'policy'=>$policy,
               'student'=>$student
            ];
            return view('dashboard.academic.edit-examination-results',$data)->withTitle('Edit Examination Results');
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Update examination results
     */
    public function update(Request $request)
    {
        try{
            DB::beginTransaction();
            $module_assignment = ModuleAssignment::with(['module','studyAcademicYear.academicYear','programModuleAssignment.campusProgram.program'])->find($request->get('module_assignment_id'));
              $academicYear = $module_assignment->studyAcademicYear->academicYear;

            $module = Module::with('ntaLevel')->find($module_assignment->module_id);
            $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
            if(!$policy){
                  return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
            }

            $student = Student::find($request->get('student_id'));

            $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type',$request->get('exam_type'))->where('status','APPROVED')->first();

            $retake_history = RetakeHistory::whereHas('moduleAssignment',function($query) use($module){
                  $query->where('module_id',$module->id);
            })->where('student_id',$student->id)->first();

            $carry_history = CarryHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

            if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$request->get('student_id'))->where('exam_type',$request->get('exam_type'))->first()){
                  $result = $res;
              }else{
                  $result = new ExaminationResult;
              }
              $result->module_assignment_id = $request->get('module_assignment_id');
                $result->student_id = $request->get('student_id');
                if($request->has('final_score')){
                $result->course_work_score = $request->get('course_work_score');
                $result->final_score = ($request->get('final_score')*$policy->final_min_mark)/100;
                }else{
                   $result->final_score = null;
                }
                if($request->get('supp_score')){
                   $result->supp_score = $request->get('supp_score');
                   $result->supp_processed_by_user_id = Auth::user()->id;
                   $result->supp_processed_at = now();
                }else{
                   $result->supp_score = null;
                   $result->supp_processed_by_user_id = Auth::user()->id;
                   $result->supp_processed_at = null;
                }
                $result->exam_type = $request->get('exam_type');
                if($carry_history){
                   $result->exam_category = 'CARRY';
                }
                if($retake_history){
                   $result->exam_category = 'RETAKE';
                }
                if($special_exam && !$request->get('final_score')){
                   $result->final_remark = 'POSTPONED';
                }else{
                   $result->final_remark = $policy->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                }
                if($result->supp_score){
                   $result->final_exam_remark = $policy->module_pass_score <= $result->supp_score? 'PASS' : 'FAIL';
                }
                $result->final_uploaded_at = now();
                $result->uploaded_by_user_id = Auth::user()->id;
                $result->save();
                DB::commit();

                return $this->processStudentResults($request,$student->id,$module_assignment->study_academic_year_id,$module_assignment->programModuleAssignment->year_of_study);

          // return redirect()->to('academic/results/'.$request->get('student_id').'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$module_assignment->programModuleAssignment->semester_id);
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request'); 
        }
    }

    /**
     * Process student results
     */
    public function processStudentResults(Request $request, $student_id, $ac_yr_id,$yr_of_study)
    {
         
         try{
            DB::beginTransaction();
            $student = Student::findOrFail($student_id);
            $campus_program = CampusProgram::with('program')->find($student->campus_program_id);
            $semester = Semester::find($request->get('semester_id'));
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
          }
            
          foreach($annual_results as $key=>$result){
                
                $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student){
                  $query->where('id',$student->id);
                    })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
               
               $student_buffer[$student->id]['annual_results'][] =  $result;
               $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
               $student_buffer[$student->id]['annual_credit'] = $annual_credit;
               foreach($optional_programs as $prog){
                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                   $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
               }
            }
          }

          foreach ($module_assignments as $assignment) {
            $results = ExaminationResult::where('module_assignment_id',$assignment->id)->where('student_id',$student->id)->get();
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
                   
                   $student_buffer[$student->id]['results'][] =  $result;
                   $student_buffer[$student->id]['year_of_study'] = $student->year_of_study;
                   $student_buffer[$student->id]['total_credit'] = $total_credit;

                   foreach($optional_programs as $prog){
                       $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                       $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;
                   }

                    $processed_result = ExaminationResult::find($result->id);
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

                return $buffer['results'];
               
               
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

           DB::commit();

           return redirect()->to('academic/results/'.$student->id.'/'.$ac_yr_id.'/'.$yr_of_study.'/show-student-results')->with('message','Results processed successfully');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display results page
     */
    public function showProgramResults(Request $request)
    {
    	$data = [
            'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'campus_programs'=>$request->has('campus_id')? CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get() : [],
            'campus'=>Campus::find($request->get('campus_id')),
            'semesters'=>Semester::all(),
            'campuses'=>Campus::all(),
            'request'=>$request
    	];

    	return view('dashboard.academic.program-results',$data)->withTitle('Final Results');
    }

    /**
     * Display results report
     */
    public function showProgramResultsReport(Request $request)
    {
      $campus_program = CampusProgram::with(['program.department','campus'])->find(explode('_',$request->get('campus_program_id'))[0]);
        $study_academic_year = StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id'));
      $semester = Semester::find($request->get('semester_id'));
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
        
        // Extract module assignments IDs
        $assignmentIds = [];
        foreach($module_assignments as $assign){
        	$assignmentIds[] = $assign->id;
        }

        if($request->get('semester_id') != 'SUPPLEMENTARY'){
           if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 1')){
              $students = Student::with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
              },'semesterRemarks.semester','examinationResults'=>function($query) use($assignmentIds){
                $query->whereIn('module_assignment_id',$assignmentIds);
              }])->where('campus_program_id',$campus_program->id)->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->get();
           }else{
              $students = Student::with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
              },'semesterRemarks.semester','annualRemarks'=>function($query) use($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'examinationResults'=>function($query) use($assignmentIds){
              	$query->whereIn('module_assignment_id',$assignmentIds);
              }])->where('campus_program_id',$campus_program->id)->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->get();
          }
        }else{
            $students = Student::with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'semesterRemarks.semester','annualRemarks'=>function($query) use($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'examinationResults'=>function($query) use($assignmentIds){
                $query->whereIn('module_assignment_id',$assignmentIds);
              },'specialExams'])->where('campus_program_id',$campus_program->id)->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->get();
        }


        if(count($students) != 0){
           if(count($students[0]->examinationResults) == 0){
              return redirect()->back()->with('error','No results processed yet for this programme');
           }
        }
        $grading_policies = GradingPolicy::where('nta_level_id',$campus_program->program->nta_level_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->orderBy('grade')->get();

        $modules = [];

        foreach($module_assignments as $assignment){
              $modules[$assignment->module->code] = [];
              $modules[$assignment->module->code]['grades'] = [];
              $modules[$assignment->module->code]['grades_perc'] = [];
              $modules[$assignment->module->code]['grades']['ML'] = [];
              $modules[$assignment->module->code]['grades']['FL'] = [];
              $modules[$assignment->module->code]['name'] = $assignment->module->name; 
              $modules[$assignment->module->code]['pass_count'] = 0;
              $modules[$assignment->module->code]['fail_count'] = 0;
              $modules[$assignment->module->code]['inc_count'] = 0;
              $modules[$assignment->module->code]['pst_count'] = 0;
              $modules[$assignment->module->code]['na_count'] = 0;
              $modules[$assignment->module->code]['ML']['pass_count'] = 0;
              $modules[$assignment->module->code]['FL']['pass_count'] = 0;
              $modules[$assignment->module->code]['ML']['pass_rate'] = 0;
              $modules[$assignment->module->code]['FL']['pass_rate'] = 0;
              $modules[$assignment->module->code]['ML']['fail_count'] = 0;
              $modules[$assignment->module->code]['FL']['fail_count'] = 0;
              $modules[$assignment->module->code]['ML']['fail_rate'] = 0;
              $modules[$assignment->module->code]['FL']['fail_rate'] = 0;
              $modules[$assignment->module->code]['ML']['inc_count'] = 0;
              $modules[$assignment->module->code]['FL']['inc_count'] = 0;
              $modules[$assignment->module->code]['ML']['pst_count'] = 0;
              $modules[$assignment->module->code]['FL']['pst_count'] = 0;
              $modules[$assignment->module->code]['ML']['na_count'] = 0;
              $modules[$assignment->module->code]['FL']['na_count'] = 0;
              $modules[$assignment->module->code]['pass_rate'] = 0;
              $modules[$assignment->module->code]['fail_rate'] = 0;
              $modules[$assignment->module->code]['na_rate'] = 0;
              $modules[$assignment->module->code]['ic_count'] = 0;
              $modules[$assignment->module->code]['ML']['ic_count'] = 0;
              $modules[$assignment->module->code]['FL']['ic_count'] = 0;
              $modules[$assignment->module->code]['if_count'] = 0;
              $modules[$assignment->module->code]['ML']['if_count'] = 0;
              $modules[$assignment->module->code]['FL']['if_count'] = 0;
              $modules[$assignment->module->code]['retake_count'] = 0;
              $modules[$assignment->module->code]['ML']['retake_count'] = 0;
              $modules[$assignment->module->code]['FL']['retake_count'] = 0;
              $modules[$assignment->module->code]['ds_count'] = 0;
              $modules[$assignment->module->code]['ML']['ds_count'] = 0;
              $modules[$assignment->module->code]['FL']['ds_count'] = 0;
              $modules[$assignment->module->code]['special_ds_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_ds_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_ds_count'] = 0;
              $modules[$assignment->module->code]['special_inc_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_inc_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_inc_count'] = 0;
              $modules[$assignment->module->code]['special_pst_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_pst_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_pst_count'] = 0;
              $modules[$assignment->module->code]['special_total_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_total_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_total_count'] = 0;
              $modules[$assignment->module->code]['special_pass_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_pass_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_pass_count'] = 0;
              $modules[$assignment->module->code]['special_fail_count'] = 0;
              $modules[$assignment->module->code]['ML']['special_fail_count'] = 0;
              $modules[$assignment->module->code]['FL']['special_fail_count'] = 0;
              $modules[$assignment->module->code]['total_count'] = count($students);
              $modules[$assignment->module->code]['ML']['total_count'] = 0;
              $modules[$assignment->module->code]['FL']['total_count'] = 0;
              $modules[$assignment->module->code]['ML']['fail_fe_count'] = 0;
              $modules[$assignment->module->code]['FL']['fail_fe_count'] = 0;
              $modules[$assignment->module->code]['fail_fe_count'] = 0;
              $modules[$assignment->module->code]['total_rate'] = 100;
              $modules[$assignment->module->code]['ML']['total_rate'] = 100;
              $modules[$assignment->module->code]['FL']['total_rate'] = 100;
              $modules[$assignment->module->code]['inc_rate'] = 0;
              $modules[$assignment->module->code]['pst_rate'] = 0;
              $modules[$assignment->module->code]['ic_rate'] = 0;
              $modules[$assignment->module->code]['if_rate'] = 0;
              $modules[$assignment->module->code]['ds_rate'] = 0;

              $modules[$assignment->module->code]['supp_pass_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_pass_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_pass_count'] = 0;
              $modules[$assignment->module->code]['supp_fail_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_fail_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_fail_count'] = 0;
              $modules[$assignment->module->code]['supp_inc_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_inc_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_inc_count'] = 0;
              $modules[$assignment->module->code]['supp_pst_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_pst_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_pst_count'] = 0;
              $modules[$assignment->module->code]['supp_total_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_total_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_total_count'] = 0;
              $modules[$assignment->module->code]['supp_carry_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_carry_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_carry_count'] = 0;
              $modules[$assignment->module->code]['supp_retake_count'] = 0;
              $modules[$assignment->module->code]['ML']['supp_retake_count'] = 0;
              $modules[$assignment->module->code]['FL']['supp_retake_count'] = 0;

            foreach($grading_policies as $policy){
              $modules[$assignment->module->code]['grades'][$policy->grade] = 0; 
              $modules[$assignment->module->code]['grades']['ML'][$policy->grade] = 0; 
              $modules[$assignment->module->code]['grades']['FL'][$policy->grade] = 0;
              $modules[$assignment->module->code]['special_grades'][$policy->grade] = 0; 
              $modules[$assignment->module->code]['special_grades']['ML'][$policy->grade] = 0; 
              $modules[$assignment->module->code]['special_grades']['FL'][$policy->grade] = 0;
              $modules[$assignment->module->code]['grades']['ML']['na_count'] = 0;
              $modules[$assignment->module->code]['grades']['FL']['na_count'] = 0;
             
            }
        }
        
        $semesters = Semester::all();
        $special_exam_first_semester_students = [];
        $special_exam_second_semester_students = [];
        $first_semester = Semester::where('name','LIKE','%1%')->first();
        $second_semester = Semester::where('name','LIKE','%2%')->first();
        $sem_modules = [];
        foreach($semesters as $sem){
           foreach($module_assignments as $assign){
            if($assign->programModuleAssignment->semester_id == $sem->id){
               $sem_modules[$sem->name][] = $assign;
            }
           }
        }

        foreach($students as $key=>$student){
            
            foreach($module_assignments as $assignment){

                     if($student->gender == 'M'){
                         $modules[$assignment->module->code]['semester_id'] = $assignment->programModuleAssignment->semester_id;          
                         $modules[$assignment->module->code]['ML']['total_count'] += 1;
                         
                      }elseif($student->gender == 'F'){
                         
                         $modules[$assignment->module->code]['FL']['total_count'] += 1;
                        
                      }
                     
                     $modules[$assignment->module->code]['ML']['total_rate'] = $modules[$assignment->module->code]['ML']['total_count']*100/count($students);

                     $modules[$assignment->module->code]['FL']['total_rate'] = $modules[$assignment->module->code]['FL']['total_count']*100/count($students);
                     

            
                foreach($student->examinationResults as $result){
                  if($result->module_assignment_id == $assignment->id){
                   
                   foreach($grading_policies as $policy){
                      if($policy->grade == $result->grade){
                   
                         $modules[$assignment->module->code]['grades'][$result->grade] += 1;

                          if($student->gender == 'M'){
                             
                             $modules[$assignment->module->code]['grades']['ML'][$result->grade] += 1;
                             
                          }elseif($student->gender == 'F'){
                             
                             $modules[$assignment->module->code]['grades']['FL'][$result->grade] += 1;
                            
                          }
                      }
                      
                         $modules[$assignment->module->code]['grades_perc'][$policy->grade] = $modules[$assignment->module->code]['grades'][$policy->grade]*100/count($students);
                      
                    }

                    $supp_ic_status = true;

                    if($result->supp_score){
                        $modules[$assignment->module->code]['supp_total_count'] += 1;

                        if($result->final_exam_remark == 'PASS'){
                            $modules[$assignment->module->code]['supp_pass_count'] += 1;
                        }else{
                            $modules[$assignment->module->code]['supp_fail_count'] += 1;
                        }
                        if($result->final_exam_remark == 'CARRY'){
                            $modules[$assignment->module->code]['supp_carry_count'] += 1;
                        }
                        if($result->final_exam_remark == 'RETAKE'){
                            $modules[$assignment->module->code]['supp_retake_count'] += 1;
                        }
                        if($student->gender == 'M'){
                           
                             $modules[$assignment->module->code]['ML']['supp_total_count'] += 1;
                              if($result->final_exam_remark == 'PASS'){
                                  $modules[$assignment->module->code]['ML']['supp_pass_count'] += 1;
                              }else{
                                  $modules[$assignment->module->code]['ML']['supp_fail_count'] += 1;
                              }
                              if($result->final_exam_remark == 'CARRY'){
                                  $modules[$assignment->module->code]['ML']['supp_carry_count'] += 1;
                              }
                              if($result->final_exam_remark == 'RETAKE'){
                                  $modules[$assignment->module->code]['ML']['supp_retake_count'] += 1;
                              }
                           
                        }elseif($student->gender == 'F'){
                           
                             $modules[$assignment->module->code]['FL']['supp_total_count'] += 1;
                              if($result->final_exam_remark == 'PASS'){
                                  $modules[$assignment->module->code]['FL']['supp_pass_count'] += 1;
                              }else{
                                  $modules[$assignment->module->code]['FL']['supp_fail_count'] += 1;
                              }
                              if($result->final_exam_remark == 'CARRY'){
                                  $modules[$assignment->module->code]['FL']['supp_carry_count'] += 1;
                              }
                              if($result->final_exam_remark == 'RETAKE'){
                                  $modules[$assignment->module->code]['FL']['supp_retake_count'] += 1;
                              }
                        }
                    }else{
                      foreach ($student->specialExams as $exam) {
                         if($exam->study_academic_year_id == $assignment->study_academic_year_id && $exam->module_assignment_id == $assignment->id && $exam->type == 'SUPP'){

                           $supp_ic_status = false;
                           
                           if($exam->semester_id == $first_semester->id){
                              $special_exam_first_semester_students[] = $student;
                           }

                           if($exam->semester_id == $second_semester->id){
                              $special_exam_second_semester_students[] = $student;
                           }

                           foreach($grading_policies as $policy){
                                if($policy->grade == $result->grade){
                             
                                   $modules[$assignment->module->code]['special_grades'][$result->grade] += 1;

                                    if($student->gender == 'M'){
                                       
                                       $modules[$assignment->module->code]['special_grades']['ML'][$result->grade] += 1;
                                       
                                    }elseif($student->gender == 'F'){
                                       
                                       $modules[$assignment->module->code]['special_grades']['FL'][$result->grade] += 1;
                                      
                                    }
                                }
                                
                              }

                           
                           if($result->final_score){
                               $modules[$assignment->module->code]['special_total_count'] += 1;
                  
                                if($student->gender == 'M'){
                                     
                                     $modules[$assignment->module->code]['ML']['special_total_count'] += 1;
                                   
                                }elseif($student->gender == 'F'){
                                   
                                     $modules[$assignment->module->code]['FL']['special_total_count'] += 1;
                                   
                                }
                            }

                            if($result->final_exam_remark == 'PASS'){
                               $modules[$assignment->module->code]['special_pass_count'] += 1;
                  
                                if($student->gender == 'M'){
                                     
                                     $modules[$assignment->module->code]['ML']['special_pass_count'] += 1;
                                   
                                }elseif($student->gender == 'F'){
                                   
                                     $modules[$assignment->module->code]['FL']['special_pass_count'] += 1;
                                   
                                }
                            }

                            if($result->final_exam_remark == 'FAIL'){
                               $modules[$assignment->module->code]['special_fail_count'] += 1;
                  
                                if($student->gender == 'M'){
                                     
                                     $modules[$assignment->module->code]['ML']['special_fail_count'] += 1;
                                   
                                }elseif($student->gender == 'F'){
                                   
                                     $modules[$assignment->module->code]['FL']['special_fail_count'] += 1;
                                   
                                }
                            }

                            if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL'){
                               $modules[$assignment->module->code]['special_ds_count'] += 1;
                  
                                if($student->gender == 'M'){
                                     
                                     $modules[$assignment->module->code]['ML']['special_ds_count'] += 1;
                                   
                                }elseif($student->gender == 'F'){
                                   
                                     $modules[$assignment->module->code]['FL']['special_ds_count'] += 1;
                                   
                                }
                            }

                            if($result->final_exam_remark == 'INCOMPLETE'){
                               $modules[$assignment->module->code]['special_inc_count'] += 1;
                  
                                if($student->gender == 'M'){
                                     
                                     $modules[$assignment->module->code]['ML']['special_inc_count'] += 1;
                                   
                                }elseif($student->gender == 'F'){
                                   
                                     $modules[$assignment->module->code]['FL']['special_inc_count'] += 1;
                                   
                                }
                            }


                            $modules[$assignment->module->code]['supp_pst_count'] += 1;
                            if($student->gender == 'M'){
                                 
                                 $modules[$assignment->module->code]['ML']['supp_pst_count'] += 1;
                               
                            }elseif($student->gender == 'F'){
                               
                                 $modules[$assignment->module->code]['FL']['supp_pst_count'] += 1;
                               
                            }
                         }
                      }
                      if($result->final_exam_remark == 'FAIL' && $supp_ic_status){
                          $modules[$assignment->module->code]['supp_inc_count'] += 1;
                            if($student->gender == 'M'){
                           
                                 $modules[$assignment->module->code]['ML']['supp_inc_count'] += 1;
                               
                            }elseif($student->gender == 'F'){
                               
                                 $modules[$assignment->module->code]['FL']['supp_inc_count'] += 1;
                    
                            }
                      }

                    }

                    if($result->exam_category == 'RETAKE'){
                       
                       $modules[$assignment->module->code]['retake_count'] += 1;
                    
                       if($student->gender == 'M'){
                           
                             $modules[$assignment->module->code]['ML']['retake_count'] += 1;
                           
                        }elseif($student->gender == 'F'){
                           
                             $modules[$assignment->module->code]['FL']['retake_count'] += 1;
                           
                        }
                    }

                    if($result->final_exam_remark == 'PASS'){
                       
                       $modules[$assignment->module->code]['pass_count'] += 1;
                    
                       if($student->gender == 'M'){
                           
                             $modules[$assignment->module->code]['ML']['pass_count'] += 1;
                           
                        }elseif($student->gender == 'F'){
                           
                             $modules[$assignment->module->code]['FL']['pass_count'] += 1;
                           
                        }
                    
                        $modules[$assignment->module->code]['pass_rate'] = $modules[$assignment->module->code]['pass_count']*100/count($students);
                    }elseif($result->final_exam_remark == 'FAIL'){
                       $modules[$assignment->module->code]['fail_count'] += 1;

                       if($student->gender == 'M'){
                             $modules[$assignment->module->code]['ML']['fail_count'] += 1;  
                       }elseif($student->gender == 'F'){ 
                             $modules[$assignment->module->code]['FL']['fail_count'] += 1;  
                       }
                       if($result->course_work_remark == 'FAIL' || $result->final_remark == 'FAIL'){
                           $modules[$assignment->module->code]['ds_count'] += 1;
                           $modules[$assignment->module->code]['ds_rate'] = $modules[$assignment->module->code]['ds_count']*100/count($students);

                           if($student->gender == 'M'){
                                 $modules[$assignment->module->code]['ML']['ds_count'] += 1;  
                           }elseif($student->gender == 'F'){ 
                                 $modules[$assignment->module->code]['FL']['ds_count'] += 1;  
                           }
                      }
                      if($result->final_remark == 'FAIL'){
                           $modules[$assignment->module->code]['fail_fe_count'] += 1;

                           if($student->gender == 'M'){
                                 $modules[$assignment->module->code]['ML']['fail_fe_count'] += 1;  
                           }elseif($student->gender == 'F'){ 
                                 $modules[$assignment->module->code]['FL']['fail_fe_count'] += 1;  
                           }
                      }
                      $modules[$assignment->module->code]['fail_rate'] = $modules[$assignment->module->code]['fail_count']*100/count($students);
                        
                    }else{
                       if($result->final_exam_remark == 'INCOMPLETE'){
                          if($result->course_work_remark == 'INCOMPLETE' && $result->final_remark == 'INCOMPLETE'){
                             $modules[$assignment->module->code]['inc_count'] += 1;

                             $modules[$assignment->module->code]['inc_rate'] = $modules[$assignment->module->code]['inc_count']*100/count($students);
                          }

                          if($result->course_work_remark == 'INCOMPLETE'){
                                $modules[$assignment->module->code]['ic_count'] += 1;
                                $modules[$assignment->module->code]['ic_rate'] = $modules[$assignment->module->code]['ic_count']*100/count($students);
                           }elseif($result->final_remark == 'INCOMPLETE'){
                              $modules[$assignment->module->code]['if_count'] += 1;
                              $modules[$assignment->module->code]['if_rate'] = $modules[$assignment->module->code]['if_count']*100/count($students);
                           }

                          if($student->gender == 'M'){
                               $modules[$assignment->module->code]['ML']['inc_count'] += 1;
                               if($result->course_work_remark == 'INCOMPLETE'){
                                  $modules[$assignment->module->code]['ML']['ic_count'] += 1;
                               }elseif($result->final_remark == 'INCOMPLETE'){
                                  $modules[$assignment->module->code]['ML']['if_count'] += 1;
                               }
                          }elseif($student->gender == 'F'){
                               $modules[$assignment->module->code]['FL']['inc_count'] += 1;
                               if($result->course_work_remark == 'INCOMPLETE'){
                                  $modules[$assignment->module->code]['FL']['ic_count'] += 1;
                               }elseif($result->final_remark == 'INCOMPLETE'){
                                  $modules[$assignment->module->code]['FL']['if_count'] += 1;
                               }
                          }
                       }elseif($result->final_exam_remark == 'POSTPONED'){
                            $modules[$assignment->module->code]['pst_count'] += 1;
                            $modules[$assignment->module->code]['pst_rate'] = $modules[$assignment->module->code]['pst_count']*100/count($students);
                          if($student->gender == 'M'){
                               $modules[$assignment->module->code]['ML']['pst_count'] += 1;
                          }elseif($student->gender == 'F'){
                               $modules[$assignment->module->code]['FL']['pst_count'] += 1;
                          }
                       }
                       $modules[$assignment->module->code]['na_count'] += 1;

                          if($student->gender == 'M'){
                               $modules[$assignment->module->code]['ML']['na_count'] += 1;
                          }elseif($student->gender == 'F'){
                               $modules[$assignment->module->code]['FL']['na_count'] += 1;
                          }

                       if($key == (count($students)-1)){
                           $modules[$assignment->module->code]['na_rate'] = $modules[$assignment->module->code]['na_count']*100/count($students);
                       }

                    }

                  }
                }
            
            }
          }
        $data = [
           'campus'=>$campus_program->campus,
           'program'=>$campus_program->program,
           'department'=>$campus_program->program->department,
           'study_academic_year'=>$study_academic_year,
           'module_assignments'=>$module_assignments,
           'students'=>$students,
           'modules'=>$modules,
           'semester'=>$semester,
           'semesters'=>$semesters,
           'sem_modules'=>$sem_modules,
           'first_semester'=>$first_semester,
           'second_semester'=>$second_semester,
           'special_exam_first_semester_students'=>$special_exam_first_semester_students,
           'special_exam_second_semester_students'=>$special_exam_second_semester_students,
           'year_of_study'=>explode('_',$request->get('campus_program_id'))[2],
           'grading_policies'=>$grading_policies,
           'staff'=>User::find(Auth::user()->id)->staff,
           'request'=>$request
        ];
        if($request->get('semester_id') != 'SUPPLEMENTARY'){
            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
               return view('dashboard.academic.reports.final-program-results-second-semester',$data)->withTitle('Final Program Results - '.$campus_program->program->name);
            }else{
               return view('dashboard.academic.reports.final-program-results-first-semester',$data)->withTitle('Final Program Results - '.$campus_program->program->name);
            }
        }else{
            return view('dashboard.academic.reports.final-program-results-supplementary',$data)->withTitle('Supplementary Program Results - '.$campus_program->program->name);
        }
    }

    /**
     * Display module results 
     */
    public function showModuleResults(Request $request)
    {
    	$data = [
            'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'campus'=>Campus::find($request->get('campus_id')),
            'semesters'=>Semester::all(),
            'campuses'=>Campus::all(),
            'campus_programs'=>$request->has('campus_id')? CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get() : [],
            'modules'=>$request->has('campus_id')? Module::whereHas('moduleAssignments.programModuleAssignment.campusProgram',function($query) use ($request){
            	$query->where('campus_id',$request->get('campus_id'));
            })->get() : [],
            'staff'=>User::find(Auth::user()->id)->staff
    	];
        return view('dashboard.academic.module-results',$data)->withTitle('Module Results');
    }

    /**
     * Display module results 
     */
    public function showModuleResultsReport(Request $request)
    {
    	$module_assignment = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($request){
    		     $query->where('campus_program_id',$request->get('campus_program_id'));
    	    })->where('module_id',$request->get('module_id'))->with('module.ntaLevel','programModuleAssignment.campusProgram.program.department','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->first();

    	if(!$module_assignment){
    		return redirect()->back()->with('error','No module assignment for selected academic year');
    	}

    	$students = Student::whereHas('examinationResults.moduleAssignment',function($query) use($request){
        	$query->where('module_id',$request->get('module_id'));
        })->with(['examinationResults.moduleAssignment'=>function($query) use($request){
        	$query->where('module_id',$request->get('module_id'));
        }])->get();

      if(count($students) != 0){
         if(count($students[0]->examinationResults) == 0){
             return redirect()->back()->with('error','No results processed yet');
         }
      }
    	$data = [
    		'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
            'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
            'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
            'module'=>$module_assignment->module,
            'study_academic_year'=>$module_assignment->studyAcademicYear,
            'module_assignment'=>$module_assignment,
            'students'=>$students,
            'staff'=>User::find(Auth::user()->id)->staff
    	];
        return view('dashboard.academic.reports.final-module-results',$data)->withTitle('Module Results');
    }

    /**
     * Display student results 
     */
    public function showStudentResults(Request $request)
    {
        return view('dashboard.academic.student-results')->withTitle('Student Results');
    }

    /**
     * Display student module results 
     */
    public function showStudentResultsReport(Request $request)
    {
    	$student = Student::with(['campusProgram.program'])->where('registration_number',$request->get('registration_number'))->first();
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

    	$data = [
    	     'years_of_studies'=>$years_of_studies,
           'student'=>$student,
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.reports.final-student-results',$data)->withTitle('Student Results');
    }

    /**
     * Display student academic year results
     */
    public function showStudentAcademicYearResults(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
         $student = Student::with(['campusProgram.program'])->find($student_id);
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id){
         	 $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student_id){
         	   $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment','moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
         	   $query->where('id',$student_id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student_id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
          'year_of_study'=>$yr_of_study,
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'optional_programs'=>$optional_programs,
          'student'=>$student,
          'staff'=>User::find(Auth::user()->id)->staff
         ];
         return view('dashboard.academic.reports.final-student-overall-results',$data)->withTitle('Student Results');
    }


    /**
     * Display uploaded modules
     */
    public function showUploadedModules(Request $request)
    {
    	$data = [
           'campus_programs'=>$request->has('campus_id')? CampusProgram::with('program')->where('campus_id',$request->get('campus_id'))->get() : [],
           'campus'=>Campus::find($request->get('campus_id')),
           'semesters'=>Semester::all(),
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'modules'=>$request->has('study_academic_year_id')? ProgramModuleAssignment::with(['module','examinationResults'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('semester_id',$request->get('semester_id'))->get() : [],
           'request'=>$request
    	];
    	return view('dashboard.academic.results-uploaded-modules',$data)->withTitle('Uploaded Modules');
    }


    /**
     * Display module students results
     */
    public function showUploadedModuleStudents(Request $request, $id)
    {
    	try{
    		$program = ProgramModuleAssignment::with(['examinationResults.student','module','campusProgram.program.department','campusProgram.campus','studyAcademicYear.academicYear'])->findOrFail($id);
	    	$data = [
	            'program_module_assignment'=>$program,
	            'module'=>$program->module,
	            'program'=>$program->campusProgram->program,
	            'department'=>$program->campusProgram->program->department,
	            'campus'=>$program->campusProgram->campus,
	            'study_academic_year'=>$program->studyAcademicYear,
	            'results_type'=>$request->get('results_type'),
              'staff'=>User::find(Auth::user()->id)->staff
	    	];
	    	return view('dashboard.academic.reports.results-uploaded-modules-students',$data)->withTitle('Module Results');
        }catch(\Exception $e){
        	return $e->getMessage();
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
