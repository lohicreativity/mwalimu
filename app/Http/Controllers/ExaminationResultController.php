<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\AcademicStatus;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\Department;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Models\SpecialExamRequest;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\OverallRemark;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\GPAClassification;
use App\Domain\Academic\Models\ExaminationProcessRecord;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Models\ExaminationResultChange;
use App\Domain\Registration\Models\Student;
use App\Domain\Settings\Models\Intake;
use App\Models\User;
use App\Utils\Util;
use Auth, DB, Validator, PDF;

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
	  $staff = User::find(Auth::user()->id)->staff;
    	$data = [
    	    'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
            'campus_programs'=>$request->has('campus_id') ? CampusProgram::with(['program.departments'])->where('campus_id',$request->get('campus_id'))->get() : [],
            'campus'=>Campus::find($request->get('campus_id')),
            'semesters'=>Semester::all(),
            'campuses'=>Campus::all(),
            'intakes'=>Intake::all(),
            'active_semester'=>Semester::where('status','ACTIVE')->first(),
            'first_semester_publish_status'=>$first_semester_publish_status,
            'second_semester_publish_status'=>$second_semester_publish_status,
            'publications'=>$request->has('study_academic_year_id')? ResultPublication::with(['studyAcademicYear.academicYear','semester','ntaLevel'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->get() : [],
            'process_records'=>ExaminationProcessRecord::whereHas('campusProgram',function($query) use ($staff){
                  $query->where('campus_id',$staff->campus_id);
               })->whereHas('campusProgram.program.departments',function($query) use ($staff){
                  $query->where('id',$staff->department_id);
               })->with(['campusProgram.program','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()->paginate(20),
            'staff'=>$staff,
            'request'=>$request
    	];
    	return view('dashboard.academic.results-processing',$data)->withTitle('Results Processing');
    }

    /**
     * Process results
     */
    public function process(Request $request)
    {
      $special_exam = SpecialExam::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('status','PENDING')->first();

      $special_exam = Postponement::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->where('status','PENDING')->first();


      if ($special_exam) {
         return redirect()->back()->with('error','There is pending request for special exams or postponement');
      }

    	DB::beginTransaction();

    	$campus_program = CampusProgram::with('program')->find(explode('_',$request->get('campus_program_id'))[0]);

      if(ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))
		  ->where('nta_level_id',$campus_program->program->nta_level_id)->where('campus_id', $campus_program->campus_id)->where('status','PUBLISHED')->count() != 0){
                  return redirect()->back()->with('error','Unable to process because results already published');
              }

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
          if($assign->programModuleAssignment->category == 'COMPULSORY'){
          	if($assign->course_work_process_status != 'PROCESSED' && $assign->module->course_work_based == 1){
              DB::rollback();
          		return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
          	}
          	if($assign->final_upload_status == null){
              DB::rollback();
          		return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
          	}
          }else{
            $exam_student_count = ProgramModuleAssignment::find($assign->program_module_assignment_id)->optedStudents()->count();
            if($assign->course_work_process_status != 'PROCESSED' && $exam_student_count != 0 && $assign->module->course_work_based == 1){
              DB::rollback();
              return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
            }
            if($assign->final_upload_status == null && $exam_student_count != 0){
              DB::rollback();
              return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
            }
          }
        }

        $student_buffer = [];
        $annual_credit = 0;

      	foreach ($module_assignments as $assignment) {
      		$results = ExaminationResult::whereHas('student.applicant',function($query) use($request){
                  $query->where('intake_id',$request->get('intake_id'));
          })->with(['retakeHistory.retakableResults'=>function($query){
                   $query->latest();
                },'carryHistory.carrableResults'=>function($query){
                   $query->latest();
                }])->where('module_assignment_id',$assignment->id)->get();

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
                   $query->where('campus_program_id',$campus_program->id)
                   ->where('category','COMPULSORY');
               })->whereNotNull('final_uploaded_at')
               ->distinct()
               ->count('module_assignment_id') < count($core_programs)){
                
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
                return redirect()->back()->with('error','Some modules are missing final marks ('.implode(',', $missing_programs).')');
            }

            $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)
            ->where('study_academic_year_id',$request->get('study_academic_year_id'))
            ->where('semester_id',$request->get('semester_id'))
            ->first();

            if($elective_policy){
              if(ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                     $query->where('campus_program_id',$campus_program->id)
                     ->where('category','OPTIONAL');
                })->whereNotNull('final_uploaded_at')
                ->distinct()
                ->count('module_assignment_id') < $elective_policy->number_of_options){
                  DB::rollback();
                  return redirect()->back()->with('error','Some optional modules are missing final marks');
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
      			$student = Student::with(['campusProgram.program.ntaLevel'])->find($result->student_id);
                  
      			
                  $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
           	        $query->where('student_id',$student->id);
                      })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$request->get('semester_id'))->where('category','OPTIONAL')->get();
                 
                 
                 $student_buffer[$student->id]['year_of_study'] = explode('_',$request->get('campus_program_id'))[2];
                 $student_buffer[$student->id]['nta_level'] = $student->campusProgram->program->ntaLevel;
                 $student_buffer[$student->id]['total_credit'] = $total_credit;
                 $student_buffer[$student->id]['opt_credit'] = 0;
                 $student_buffer[$student->id]['opt_prog'] = 0;
                 $student_buffer[$student->id]['opt_prog_status'] = true;
                 //$student_buffer[$student->id]['results'] = [];

                 $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))->first();

                 foreach($optional_programs as $prog){
                     $student_buffer[$student->id]['opt_credit'] += $prog->module->credit; 
                     $student_buffer[$student->id]['opt_prog'] += 1;
                 }
                 if($elective_policy){
                 if($student_buffer[$student->id]['opt_prog'] < $elective_policy->number_of_options){
                     $student_buffer[$student->id]['opt_prog_status'] = false;
                 }
                 }
                 // $student_buffer[$student->id]['opt_prog_status'] = $elective_policy->number_of_options ;
                 $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;

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
                     
                  //   $processed_result->course_work_remark = $assignment->programModuleAssignment->course_work_pass_score <= $processed_result->course_work_score ? 'PASS' : 'FAIL';

                  //   $processed_result->final_remark = $assignment->programModuleAssignment->final_pass_score <= $processed_result->final_score? 'PASS' : 'FAIL';

                  // 	$processed_result->total_score = round($result->course_work_score + $result->final_score);

                     if ($assignment->module->course_work_based == 1) {
                        $processed_result->course_work_remark = $assignment->programModuleAssignment->course_work_pass_score <= $processed_result->course_work_score ? 'PASS' : 'FAIL';

                        $processed_result->final_remark = $assignment->programModuleAssignment->final_pass_score <= $processed_result->final_score? 'PASS' : 'FAIL';

                        $processed_result->total_score = round($result->course_work_score + $result->final_score);
                     } else {

                        $processed_result->course_work_remark = 'N/A';
                        $processed_result->final_remark = $assignment->programModuleAssignment->final_pass_score <= $processed_result->final_score? 'PASS' : 'FAIL';
                        $processed_result->total_score = $result->final_score;

                     }
                  }

                  $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)
                  ->where('study_academic_year_id',$assignment->studyAcademicYear->id)
                  ->where('min_score','<=',round($processed_result->total_score))
                  ->where('max_score','>=',round($processed_result->total_score))
                  ->first();


    
                  if(!$grading_policy){
                     return $assignment->module->name;
                     return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
                  }
                  
                  if($result->course_work_remark == 'INCOMPLETE' || $result->final_remark == 'INCOMPLETE' || $result->final_remark == 'POSTPONED'){
                  	$processed_result->grade = null;
                      $processed_result->point = null;
                      if($processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                          $processed_result->final_exam_remark = $processed_result->final_remark;
                      }
                      if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->course_work_remark == 'POSTPONED'){
                          $processed_result->final_exam_remark = $processed_result->course_work_remark;
                      }
                  }else{
                  	$processed_result->grade = $grading_policy? $grading_policy->grade : null;
                      $processed_result->point = $grading_policy? $grading_policy->point : null;
                      if($processed_result->course_work_remark == 'FAIL' || $processed_result->final_remark == 'FAIL'){

                        if ($processed_result->supp_processed_at) {
                           $processed_result->final_exam_remark = 'PASS';
                           $processed_result->grade = 'C';
                           $processed_result->point = 1;
                        } else {

                           $processed_result->final_exam_remark = 'FAIL';
                           $processed_result->grade = 'F';
                           $processed_result->point = 0;

                        }

                           // $processed_result->final_exam_remark = 'FAIL';
                           // $processed_result->grade = 'F';
                           // $processed_result->point = 0;

                         
                      }else{
                        $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';
                      }
                      
                      if($request->get('semester_id') == 'SUPPLEMENTARY'){

                        

                        if($processed_result->supp_score){ 
                             if($processed_result->supp_score < $assignment->programModuleAssignment->module_pass_mark){
                                 $processed_result->grade = 'F';
                                 $processed_result->point = 0;
                                 $processed_result->supp_remark = 'FAIL';
                             }else{
                                $processed_result->grade = 'C';
                                $processed_result->point = 2;
                                $processed_result->supp_remark = 'PASS';
                             }


                          	if(Util::stripSpacesUpper($assignment->module->ntaLevel->name) == Util::stripSpacesUpper('NTA Level 7')){
                                  if($assignment->programModuleAssignment->year_of_study == 1){
                                       if($processed_result->retakable_id != null){
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                       }else{
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'CARRY';
                                       }
                                  }else{
                                       if($processed_result->retakable_id != null){
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                       }else{
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                                       }
                                  }
                                  
                          	}else{
                                  if($processed_result->retakable_id != null){
                                      $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                  }else{
                                      $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                                  }
                          	}
                            
                          	$processed_result->supp_processed_at = now();
                          	$processed_result->supp_processed_by_user_id = Auth::user()->id;
                        	}else{
                            if($processed_result->supp_remark == 'INCOMPLETE'){
                                $processed_result->final_exam_remark = 'INCOMPLETE';
                            }
                          }

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

                            $exam_row = ExaminationResult::find($processed_result->id);
                            $exam_row->retakable_id = $history->id;
                            $exam_row->retakable_type = 'retake_history';
                            $exam_row->save();
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

                            $exam_row = ExaminationResult::find($processed_result->id);
                            $exam_row->retakable_id = $history->id;
                            $exam_row->retakable_type = 'carry_history';
                            $exam_row->save();
                           
                           
                      	}

      		  }
      	   }
          
        
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


            $student = Student::with(['campusProgram.program.ntaLevel'])->find($result->student_id);
                  
                  $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                    $query->where('student_id',$student->id);
                      })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                 
                 if(!isset($student_buffer[$student->id]['results'])){
                    $student_buffer[$student->id]['results'] = [];
                    $student_buffer[$student->id]['total_credit'] = 0;
                 }

                 // undefined array key "opt_prog_status" toyota

                 $student_buffer[$student->id]['nta_level'] = $student->campusProgram->program->ntaLevel;
                 $student_buffer[$student->id]['annual_results'][] =  $result;
                 $student_buffer[$student->id]['year_of_study'] = explode('_',$request->get('campus_program_id'))[2];
                 $student_buffer[$student->id]['annual_credit'] = $annual_credit;
                 $student_buffer[$student->id]['opt_prog_status'] = true;
                 $student_buffer[$student->id]['opt_credit'] = 0;
                 foreach($optional_programs as $prog){
                     $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                     $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
                 }
            }

            
        }
        
        foreach($student_buffer as $key=>$buffer){

         
                 $pass_status = 'PASS';
                 $supp_exams = [];
                 $retake_exams = [];
                 $carry_exams = [];
                 $student = Student::with(['campusProgram.program.ntaLevel'])->find($key);
              if(isset($buffer['results'])){
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

                    if($res->final_exam_remark == 'REPEAT'){
                       $pass_status = 'REPEAT'; 
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
                        $remark->remark = ($buffer['opt_prog_status'])? $pass_status : 'INCOMPLETE';
                        if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                             $remark->gpa = null;
                        }else{
                           $remark->gpa = Util::computeGPA($buffer['total_credit'],$buffer['results']);
                        }
                        $remark->point = Util::computeGPAPoints($buffer['total_credit'],$buffer['results']);
                        $remark->credit = $buffer['total_credit'];
                        $remark->year_of_study = $buffer['year_of_study'];
                        $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                        if($remark->gpa && $gpa_class){
                          $remark->class = $gpa_class->name;
                        }else{
                          $remark->class = null;
                        }
						//return $gpa_class->name;
                        $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                        $remark->save();
                 }



                 if($request->get('semester_id') == 'SUPPLEMENTARY'){

                     $sem_remarks = SemesterRemark::with(['student'])->where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->get();


                     foreach ($sem_remarks as $rem) {
                        $mod_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$rem){
                            $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$rem->semester_id);
                          })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                        $query->where('program_id',$campus_program->program->id);
                          })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();


                        $stud_buffer = [];
                        $ann_credit = 0;

                        $elective_policy = ElectivePolicy::where('campus_program_id',$rem->student->campus_program_id)->where('study_academic_year_id',$rem->study_academic_year_id)->where('semester_id',$rem->semester_id)->first();


                        foreach ($mod_assignments as $assignment) {
                          $results = ExaminationResult::whereHas('student.applicant',function($query) use($request){
                                  $query->where('intake_id',$request->get('intake_id'));
                          })->with(['retakeHistory.retakableResults'=>function($query){
                                   $query->latest();
                                },'carryHistory.carrableResults'=>function($query){
                                   $query->latest();
                                }])->where('module_assignment_id',$assignment->id)->where('student_id',$key)->get();
                  
                            $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();

                          $tot_credit = 0;


                          
                          foreach($core_programs as $prog){
                            
                              $ann_credit += $prog->module->credit;

                            if($prog->semester_id == $rem->semester_id){
                              $tot_credit += $prog->module->credit;
                            }
                          }

                            foreach($results as $resKey=>$result){

                            $std = Student::with(['campusProgram.program.ntaLevel'])->find($result->student_id);
                                  
                            
                                  $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($std){
                                    $query->where('student_id',$std->id);
                                      })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$rem->semester_id)->where('category','OPTIONAL')->get();

                                 $stud_buffer[$key]['total_credit'] = $total_credit;
                                 $stud_buffer[$key]['opt_credit'] = 0;
                                 $stud_buffer[$key]['opt_prog_status'] = true;
                                 $stud_buffer[$key]['opt_prog'] = 0;
                                 $stud_buffer[$key]['results'][] = $result;

                                 foreach($optional_programs as $prog){
                                     $stud_buffer[$key]['opt_credit'] += $prog->module->credit;
                                     $stud_buffer[$key]['opt_prog'] += 1; 
                                 }
								 if($elective_policy){									 
									 if($stud_buffer[$key]['opt_prog_status'] < $elective_policy->number_of_options){
										$stud_buffer[$key]['opt_prog_status'] = false;
									 }
								 }
                                 $stud_buffer[$key]['total_credit'] = $stud_buffer[$key]['opt_credit'] + $tot_credit;           

                           }
                        }

                        foreach($stud_buffer as $bufKey=>$buf){

                             $sem_pass_status = 'PASS';
                             $supp_exams = [];
                             $retake_exams = [];
                             $carry_exams = [];
                          if(isset($buf['results'])){
                             foreach($buf['results'] as $res){
                                if($res->final_exam_remark == 'INCOMPLETE'){
                                    $sem_pass_status = 'INCOMPLETE';
                                    break;
                                }

                                if($res->final_exam_remark == 'POSTPONED'){
                                    $sem_pass_status = 'POSTPONED';
                                    break;
                                }

                                if($res->final_exam_remark == 'RETAKE'){
                                    $sem_pass_status = 'RETAKE'; 
                                    $retake_exams[] = $res->moduleAssignment->module->code;
                                    break;
                                }  

                                if($res->final_exam_remark == 'CARRY'){
                                    $sem_pass_status = 'CARRY'; 
                                    $carry_exams[] = $res->moduleAssignment->module->code;
                                    break;
                                } 

                                if($res->final_exam_remark == 'REPEAT'){
                                   $sem_pass_status = 'REPEAT'; 
                                   break;
                                }

                                if($res->final_exam_remark == 'FAIL'){
                                    $sem_pass_status = 'SUPP'; 
                                    $supp_exams[] = $res->moduleAssignment->module->code;
                                }   
                             }
                           }
                         }
          


                        $remark = SemesterRemark::find($rem->id);
                        $remark->study_academic_year_id = $request->get('study_academic_year_id');
                        $remark->student_id = $key;
                        $remark->remark = ($stud_buffer[$key]['opt_prog_status'])? $sem_pass_status : 'INCOMPLETE';
                        if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                             $remark->gpa = null;
                        }else{
                           $remark->gpa = Util::computeGPA($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                        }
                        $remark->point = Util::computeGPAPoints($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                        $remark->credit = $stud_buffer[$key]['total_credit'];
                        $remark->year_of_study = $buffer['year_of_study'];
                        $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                        $remark->save();
                     }  


                      $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->get();


                       if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
                          $remark = $rm;
                          $remark->student_id = $key;
                          $remark->year_of_study = $buffer['year_of_study'];
                          $remark->study_academic_year_id = $request->get('study_academic_year_id');
                          $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
                          

                          if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                             $remark->gpa = null;

                          }else{

                               $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                               if($remark->gpa < 2.0){
                                   $remark->remark = 'FAIL&DISCO';
                               }
                               $remark->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                               $remark->credit = $buffer['annual_credit'];
                          } 
                          $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                          if($remark->gpa && $gpa_class){
                            $remark->class = $gpa_class->name;
                          }else{
                            $remark->class = null;
                          }
                          $remark->save();

                          $status = AcademicStatus::where('name',$remark->remark)->first();


                          $stud = Student::find($key);
                          $stud->academic_status_id = $status->id;
                          $stud->save();
                        
                       }



                       if($student_buffer[$key]['year_of_study'] == $student->year_of_study){
                      
                              $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                              $results = ExaminationResult::where('student_id',$key)->get();
                              $points = 0;
                              $credits = 0;

                              foreach($results as $rs){
                                   if(!is_null($rs->point)){
                                      $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                                      $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                                   }   
                              }
                              
                              $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                              $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                              // if(!$gpa_class){
                               //  return redirect()->back()->with('error','GPA classification not defined');
                              // }
                              if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study){
                                 $overall_remark = $gpa_class->name;

                                 if($rm = OverallRemark::where('student_id',$key)->first()){
                                    $remark = $rm;
                                 }else{
                                    $remark = new OverallRemark;
                                 }
                                 $remark->student_id = $key;
                                 $remark->point = $points;
                                 $remark->credit = $credits;
                                 $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                                 
                                 if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                                    $remark->remark = null;
                                    $remark->class = null;
                                 }else{
                                    $remark->remark = Util::getOverallRemark($sem_remarks);
                                    $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                                 }
                                 $remark->save();
                              }
                            }
                            
                 }else{
                     $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->get();
                     //if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
                         

                         if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$buffer['year_of_study'])->first()){
                            $remark = $rm;
                            
                         }else{
                            $remark = new AnnualRemark;
                         }
                            $remark->student_id = $key;
                            $remark->year_of_study = $buffer['year_of_study'];
                            $remark->study_academic_year_id = $request->get('study_academic_year_id');
                            $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
                            //removed duplicate remark of incomplete
                            if($remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                              if($remark->remark == 'SUPP'){
                                 $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                                 if($remark->gpa < 2.0){
                                    $remark->remark = 'FAIL&DISCO';
                                    $remark->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                                    $remark->credit = $buffer['annual_credit'];
                                 }else{
                                    $remark->gpa = null;
                                 }
                              }else {
                                 $remark->gpa = null;
                              }
                            }else{
                                 $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                                 if($remark->gpa < 2.0){
                                    $remark->remark = 'FAIL&DISCO';
                                 }
                                 $remark->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                                 $remark->credit = $buffer['annual_credit'];
                            }
                            if($sem_remarks[0]->remark == 'POSTPONED' && $sem_remarks[(count($sem_remarks)-1)]->remark != 'POSTPONED'){
                              //update annual remark
                              //   $remark->remark = $sem_remarks[(count($sem_remarks)-1)]->remark;
                              $remark->remark = $sem_remarks[0]->remark;
                            }
                           $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                            if($remark->gpa && $gpa_class){
                              $remark->class = $gpa_class->name;
                            }else{
                              $remark->class = null;
                            }
                            $remark->save();

                            $status = AcademicStatus::where('name',$remark->remark)->first();
                            $stud = Student::find($key);
                            $stud->academic_status_id = $status->id;
                            $stud->save();

                            if($student_buffer[$key]['year_of_study'] == $student->year_of_study){
                      
                              $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                              $results = ExaminationResult::where('student_id',$key)->get();
                              $points = 0;
                              $credits = 0;

                              foreach($results as $rs){
                                   if(!is_null($rs->point)){
                                      $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                                      $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                                   }   
                              }
                              
                              $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                              $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                              // if(!$gpa_class){
                               //  return redirect()->back()->with('error','GPA classification not defined');
                              // }
                              if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study){
                                 $overall_remark = $gpa_class->name;

                                 if($rm = OverallRemark::where('student_id',$key)->first()){
                                    $remark = $rm;
                                 }else{
                                    $remark = new OverallRemark;
                                 }
                                 $remark->student_id = $key;
                                 $remark->point = $points;
                                 $remark->credit = $credits;
                                 $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                                 
                                 if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                                    $remark->remark = null;
                                   $remark->class = null;
                                 }else{
                                    $remark->remark = Util::getOverallRemark($sem_remarks);
                                    $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                                 }
                                 $remark->save();
                              }
                            }
                           ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',0)->where('nta_level_id',$campus_program->program->nta_level_id)->delete();
                        //}
                       
                   }
                 }

                  

                 if($pub = ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))
						   ->where('nta_level_id',$campus_program->program->nta_level_id)->where('campus_id', $campus_program->campus_id)->first()){
					$publication = $pub;
                 }else{
                    $publication = new ResultPublication;
					$publication->study_academic_year_id = $request->get('study_academic_year_id');
					$publication->semester_id = $request->get('semester_id') == 'SUPPLEMENTARY'? 0 : $request->get('semester_id');
					$publication->type = $request->get('semester_id') == 'SUPPLEMENTARY'? 'SUPP' : 'FINAL';
					$publication->campus_id = $campus_program->campus_id;
					$publication->nta_level_id = $campus_program->program->nta_level_id;
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
    public function create(Request $request, $student_id,$ac_yr_id,$yr_of_study, $semester_id)
    {
        try{
            $student = Student::findOrFail($student_id);
            $results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])
            ->where('student_id',$student->id)
            ->get();
            $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study,$semester_id){
                   $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('semester_id',$semester_id);
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study,$semester_id){
                     $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->where('semester_id',$semester_id);
                })->get();

              $moduleIds = [];

              foreach ($core_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }

              foreach ($opt_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }
              
              $missing_modules = [];
              foreach ($core_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[] = $module;
                 }
              }
              foreach ($opt_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[] = $module;
                 }
              }

            $data = [
               'missing_modules'=>$missing_modules,
               'student'=>Student::find($student->id),
               'staff'=>User::find(Auth::user()->id)->staff
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
            $module_assignment = ModuleAssignment::with(['module','programModuleAssignment'])->where('program_module_assignment_id',$prog_id)->first();
            if(Auth::user()->hasRole('staff')){
              
              if(ExaminationResult::where('module_assignment_id',$module_assignment->id)->whereNotNull('final_processed_at')->count() != 0){
                  return redirect()->back()->with('error','Unable to edit results because results already processed');
              }
            }

            if(Auth::user()->hasRole('hod')){
              
              if(ResultPublication::where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('nta_level_id',$module_assignment->module->nta_level_id)->where('status','PUBLISHED')->count() != 0){
                  return redirect()->back()->with('error','Unable to edit results because results already published');
              }
            }
            $student = Student::findOrFail($student_id);
            $result = ExaminationResult::whereHas('moduleAssignment.programModuleAssignment',function($query) use($prog_id,$ac_yr_id){
                    $query->where('id',$prog_id)->where('study_academic_year_id',$ac_yr_id);
                 })->with(['moduleAssignment.programModuleAssignment.module.ntaLevel','moduleAssignment.programModuleAssignment.campusProgram.program'])->where('student_id',$student->id)->firstOrFail();
            $policy = ExaminationPolicy::where('nta_level_id',$result->moduleAssignment->programModuleAssignment->module->ntaLevel->id)->where('study_academic_year_id',$result->moduleAssignment->study_academic_year_id)->where('type',$result->moduleAssignment->programModuleAssignment->campusProgram->program->category)->first();

            $data = [
               'result'=>$result,
               'policy'=>$policy,
               'student'=>$student,
               'module_assignment'=>$module_assignment
            ];
            return view('dashboard.academic.edit-examination-results',$data)->withTitle('Edit Examination Results');
        }catch(\Exception $e){
			return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    public function updateStudentResults(Request $request, $module_id, $student_id, $ac_yr_id, $yr_of_study, $process_type = null)
    {
      try{
         DB::beginTransaction();
         $student = Student::findOrFail($student_id);
         $campus_program = CampusProgram::with(['program.ntaLevel'])->find($student->campus_program_id);
         $semester = Semester::find($request->get('semester_id'));

         $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study){
            $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study);
           })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
          $query->where('program_id',$campus_program->program->id);
        })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

        $annual_module_assignments = $module_assignments;

         $module_assignment = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study){
            $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study)->where('semester_id',$request->get('semester_id'));
          })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
        $query->where('program_id',$campus_program->program->id);
          })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('module_assignments.id', $module_id)->where('study_academic_year_id',$ac_yr_id)->first();


          if($module_assignment->programModuleAssignment->category == 'COMPULSORY'){
            if($module_assignment->course_work_process_status != 'PROCESSED' && $module_assignment->module->course_work_based == 1){
              DB::rollback();
              return redirect()->back()->with('error',$module_assignment->module->name.'-'.$module_assignment->module->code.' course works not processed');
            }
            if($module_assignment->final_upload_status == null){
              DB::rollback();
              return redirect()->back()->with('error',$module_assignment->module->name.'-'.$module_assignment->module->code.' final not uploaded');
            }
          }else{
            $exam_student_count = ProgramModuleAssignment::find($module_assignment->program_module_assignment_id)->optedStudents()->count();
            if($module_assignment->course_work_process_status != 'PROCESSED' && $exam_student_count != 0 && $module_assignment->module->course_work_based == 1){
              DB::rollback();
              return redirect()->back()->with('error',$module_assignment->module->name.'-'.$module_assignment->module->code.' course works not processed');
            }
            if($module_assignment->final_upload_status == null && $exam_student_count != 0){
              DB::rollback();
              return redirect()->back()->with('error',$module_assignment->module->name.'-'.$module_assignment->module->code.' final not uploaded');
            }
          }

          $student_buffer = [];
          $annual_credit = 0;

            $result = ExaminationResult::with(['retakeHistory.retakableResults'=>function($query){
            $query->latest();
            },'carryHistory.carrableResults'=>function($query){
               $query->latest();
            }])->where('module_assignment_id',$module_id)->where('student_id',$student->id)->first();

            $policy = ExaminationPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
     
             
            if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
               $core_programs = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study,$module_assignment){
                  $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study)->where('semester_id',$request->get('semester_id'))->where('category','COMPULSORY')->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id);
               })->with(['module'])->where('module_assignments.id', $module_id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->first();
                              
            }else{
               $core_programs = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$student,$yr_of_study,$module_assignment){
                  $query->where('campus_program_id',$student->campus_program_id)->where('year_of_study',$yr_of_study)->where('semester_id',$request->get('semester_id'))->where('category','COMPULSORY')->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id);
               })->with(['module'])->where('module_assignments.id', $module_id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->first();
            }

            $total_credit = 0;

            $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester->id)->first();


            if($core_programs){
               if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){          
                  $annual_credit += $core_programs->module->credit;
                  }
   
                if($core_programs->programModuleAssignment->semester_id == $request->get('semester_id')){
                   $total_credit += $core_programs->module->credit;
                  }
            }
                       
               $student_buffer[$student->id]['opt_credit'] = 0;
               $student_buffer[$student->id]['opt_prog'] = 0;
               $student_buffer[$student->id]['opt_prog_status'] = true;

               $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                  $query->where('student_id',$student->id);
                    })->whereHas('moduleAssignments', function($query) use($module_id){
                     $query->where('id',$module_id);
                       })->with(['module'])->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$request->get('semester_id'))->where('category','OPTIONAL')->first();

               if($optional_programs){
                  $student_buffer[$student->id]['opt_credit'] += $optional_programs->module->credit;
                  $student_buffer[$student->id]['opt_prog'] += 1; 
                  if($student_buffer[$student->id]['opt_prog'] < $elective_policy->number_of_options){
                     $student_buffer[$student->id]['opt_prog_status'] = false;
                  }
               }

               $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;

               if($result->retakeHistory && isset($result->retakeHistory->retakableResults[0])){
                  $processed_result = ExaminationResult::find($result->retakeHistory->retakableResults[0]->id);
                  
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

            $grading_policy = GradingPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->total_score))->where('max_score','>=',round($processed_result->total_score))->first();

            if($processed_result->appeal_score){
               $grading_policy = GradingPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->appeal_score))->where('max_score','>=',round($processed_result->appeal_score))->first();
             }

             if(!$grading_policy){
               // DB::rollback();
               return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
            }

            if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
               $processed_result->grade = null;
                 $processed_result->point = null;
               if($processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                   $processed_result->final_exam_remark = $processed_result->final_remark;
               }
               if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->course_work_remark == 'POSTPONED'){
                   $processed_result->final_exam_remark = $processed_result->course_work_remark;
               }
             }else {
               $processed_result->grade = $grading_policy? $grading_policy->grade : null;
               $processed_result->point = $grading_policy? $grading_policy->point : null;
               if($processed_result->course_work_remark == 'FAIL' || $processed_result->final_remark == 'FAIL'){

                  if ($processed_result->supp_processed_at && $processed_result->final_exam_remark == 'CARRY') {
                     $processed_result->final_exam_remark = 'CARRY';
                     $processed_result->grade = 'F';
                     $processed_result->point = 0;
                  }elseif($processed_result->supp_processed_at && $processed_result->final_exam_remark == 'RETAKE'){
                     $processed_result->final_exam_remark = 'RETAKE';
                     $processed_result->grade = 'F';
                     $processed_result->point = 0;
                  } elseif ($processed_result->supp_processed_at) { 

                     $processed_result->final_exam_remark = 'PASS';
                     $processed_result->grade = 'C';
                     $processed_result->point = 1;

                  } else  {

                     $processed_result->final_exam_remark = 'FAIL';
                     $processed_result->grade = 'F';
                     $processed_result->point = 0;

                  }

                  // $processed_result->final_exam_remark = 'FAIL';
                  // $processed_result->grade = 'F';
                  // $processed_result->point = 0;

               }else{
                 $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';
               }

               if($processed_result->supp_score){
                  if(Util::stripSpacesUpper($module_assignment->module->ntaLevel->name) == Util::stripSpacesUpper('NTA Level 7')){
                          if($module_assignment->programModuleAssignment->year_of_study == 1){
                               if($processed_result->retakable_id != null){
                                     $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                  // if ($assignment->id == $processed_result->carryHistory->module_assignment_id) {
                                  //    $processed_result->final_exam_remark = 'CARRY';
                                  //    # code...
                                  // } else {
                                  //    $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                  // }
                               }else{
                                    $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'CARRY';
                               }
                          }else{
                               if($processed_result->retakable_id != null){
                                    $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                               }else{
                                    $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                               }
                          }
                          
                    }else{
                          if($processed_result->retakable_id != null){
                              $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                          }else{
                              $processed_result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                          }
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
            $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;

            if($processed_result->final_exam_remark == 'RETAKE'){
               if($hist = RetakeHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->first()){
                 $history = $hist;
               }else{
                 $history = new RetakeHistory;
               }

               $history->student_id = $student->id;
               $history->study_academic_year_id = $ac_yr_id;
               $history->module_assignment_id = $module_assignment->id;
               $history->examination_result_id = $processed_result->id;
               $history->save();

               $exam_row = ExaminationResult::find($processed_result->id);
               $exam_row->retakable_id = $history->id;
               $exam_row->retakable_type = 'retake_history';
               $exam_row->save();
             }

             if($processed_result->final_exam_remark == 'CARRY'){
               if($hist = CarryHistory::where('study_academic_year_id',$ac_yr_id)->where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->first()){
                 $history = $hist;
               }else{
                 $history = new CarryHistory;
               }

               $history->student_id = $student->id;
               $history->study_academic_year_id = $ac_yr_id;
               $history->module_assignment_id = $module_assignment->id;
               $history->examination_result_id = $processed_result->id;
               $history->save();

               $exam_row = ExaminationResult::find($processed_result->id);
               $exam_row->retakable_id = $history->id;
               $exam_row->retakable_type = 'retake_history';
               $exam_row->save();
             }


             foreach ($annual_module_assignments as $assign) {
               $annual_results = ExaminationResult::with(['moduleAssignment.module'])->where('module_assignment_id',$assign->id)->where('student_id',$student->id)->get();
   
               if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
   
                 $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
               }else{
                 $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
               }
         
             $annual_credit = 0;
             $student_buffer[$student->id]['opt_credit'] = 0;
             foreach($core_programs as $prog){            
                   $annual_credit += $prog->module->credit;
             }
               
             foreach($annual_results as $key=>$result){
                   
                   $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                     $query->where('student_id',$student->id);
                       })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                  if(!isset($student_buffer[$student->id]['results'])){
                       $student_buffer[$student->id]['results'] = [];
                       $student_buffer[$student->id]['total_credit'] = 0;
                    }
                   $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;
                  $student_buffer[$student->id]['annual_results'][] =  $result;
                  $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
                  $student_buffer[$student->id]['annual_credit'] = $annual_credit;
                  foreach($optional_programs as $prog){
                      $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                      $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
                  }
   
               }
           }

             foreach ($annual_module_assignments as $assign) {
               $annual_results = ExaminationResult::with(['moduleAssignment.module'])->where('module_assignment_id',$assign->id)->where('student_id',$student->id)->get();
   
               if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){
   
                 $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
               }else{
                 $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
               }
         
             $annual_credit = 0;
             $student_buffer[$student->id]['opt_credit'] = 0;
             foreach($core_programs as $prog){            
                   $annual_credit += $prog->module->credit;
             }
               
             foreach($annual_results as $key=>$result){
                   
                   $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                     $query->where('student_id',$student->id);
                       })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                  if(!isset($student_buffer[$student->id]['results'])){
                       $student_buffer[$student->id]['results'] = [];
                       $student_buffer[$student->id]['total_credit'] = 0;
                    }
                   $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;
                  $student_buffer[$student->id]['annual_results'][] =  $result;
                  $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
                  $student_buffer[$student->id]['annual_credit'] = $annual_credit;
                  foreach($optional_programs as $prog){
                      $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                      $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
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
   
                     if($res->final_exam_remark == 'REPEAT'){
                         $pass_status = 'REPEAT'; 
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
                   $remark->remark = ($buffer['opt_prog_status'])? $pass_status : 'INCOMPLETE';
                   if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                        $remark->gpa = null;
                   }else{
                      $remark->gpa = Util::computeGPA($buffer['total_credit'],$buffer['results']);
                      $remark->point = Util::computeGPAPoints($buffer['total_credit'],$buffer['results']);
                      $remark->credit = $buffer['total_credit'];
                   }
                   $remark->year_of_study = $buffer['year_of_study'];
                   $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                   $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                   if($remark->gpa && $gpa_class){
                     $remark->class = $gpa_class->name;
                   }else{
                     $remark->class = null;
                   }
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
                       if($rem->remark == 'INCOMPLETE' || $rem->remark == 'INCOMPLETE' || $rem->remark == 'POSTPONED' || $rem->remark == 'SUPP'){
                          $rem->gpa = null;
                       }else{
                            $rem->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                            if($rem->gpa < 2.0){
                               $rem->remark = 'FAIL&DISCO';
                            }
                            $rem->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                            $rem->credit = $buffer['annual_credit'];
                       }
                       if($sem_remarks[0]->remark == 'POSTPONED' && $sem_remarks[(count($sem_remarks)-1)]->remark != 'POSTPONED'){
                                  $rem->remark = $sem_remarks[(count($sem_remarks)-1)]->remark;
                               }
                       $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($rem->gpa,1,1))->where('max_gpa','>=',bcdiv($rem->gpa,1,1))->first();
                       if($rem->gpa && $gpa_class){
                         $rem->class = $gpa_class->name;
                       }else{
                         $rem->class = null;
                       }
                       $rem->save();
   
                       $status = AcademicStatus::where('name',$remark->remark)->first();
   
                       $stud = Student::find($key);
                       $stud->academic_status_id = $status->id;
                       $stud->save();
   
                       if($process_type == 'SUPP'){
                        $sem_remarks = SemesterRemark::with(['student'])->where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();
   
   
   
                        foreach ($sem_remarks as $rem) {
                           $mod_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$rem,$yr_of_study,$campus_program){
                               $query->where('campus_program_id',$campus_program->id)->where('year_of_study',$yr_of_study)->where('semester_id',$rem->semester_id);
                             })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                           $query->where('program_id',$campus_program->program->id);
                             })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();
   
                           $stud_buffer = [];
                           $ann_credit = 0;
   
                           $elective_policy = ElectivePolicy::where('campus_program_id',$rem->student->campus_program_id)->where('study_academic_year_id',$rem->study_academic_year_id)->where('semester_id',$rem->semester_id)->first();
   
                           foreach ($mod_assignments as $assignment) {
                             $results = ExaminationResult::whereHas('student.applicant',function($query) use($request){
                                     $query->where('intake_id',$request->get('intake_id'));
                             })->with(['retakeHistory.retakableResults'=>function($query){
                                      $query->latest();
                                   },'carryHistory.carrableResults'=>function($query){
                                      $query->latest();
                                   }])->where('module_assignment_id',$assignment->id)->where('student_id',$key)->get();
                     
                               $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
   
                             $tot_credit = 0;
                             
                             foreach($core_programs as $prog){
                               
                                    $ann_credit += $prog->module->credit;
   
                               if($prog->semester_id == $rem->semester_id){
                                 $tot_credit += $prog->module->credit;
                               }
                             }
   
                               foreach($results as $resKey=>$result){
                               $std = Student::with(['campusProgram.program.ntaLevel'])->find($result->student_id);
                                     
                               
                                     $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($std){
                                       $query->where('student_id',$std->id);
                                         })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$rem->semester_id)->where('category','OPTIONAL')->get();
                                   
                                    $stud_buffer[$key]['total_credit'] = $total_credit;
                                    $stud_buffer[$key]['opt_credit'] = 0;
                                    $stud_buffer[$key]['opt_prog_status'] = true;
                                    $stud_buffer[$key]['opt_prog'] = 0;
                                    $stud_buffer[$key]['results'][] = $result;
   
                                    foreach($optional_programs as $prog){
                                        $stud_buffer[$key]['opt_credit'] += $prog->module->credit;
                                        $stud_buffer[$key]['opt_prog'] += 1; 
                                    }
                                    if($stud_buffer[$key]['opt_prog_status'] < $elective_policy->number_of_options){
                                       $stud_buffer[$key]['opt_prog_status'] = false;
                                    }
                                    $stud_buffer[$key]['total_credit'] = $stud_buffer[$key]['opt_credit'] + $tot_credit;           
   
                               }
                           }
   
                           foreach($stud_buffer as $bufKey=>$buf){
                                $sem_pass_status = 'PASS';
                                $supp_exams = [];
                                $retake_exams = [];
                                $carry_exams = [];
                             if(isset($buf['results'])){
                                foreach($buf['results'] as $res){
                                   if($res->final_exam_remark == 'INCOMPLETE'){
                                       $sem_pass_status = 'INCOMPLETE';
                                       break;
                                   }
   
                                   if($res->final_exam_remark == 'POSTPONED'){
                                       $sem_pass_status = 'POSTPONED';
                                       break;
                                   }
   
                                   if($res->final_exam_remark == 'RETAKE'){
                                       $sem_pass_status = 'RETAKE'; 
                                       $retake_exams[] = $res->moduleAssignment->module->code;
                                       break;
                                   }  
   
                                   if($res->final_exam_remark == 'CARRY'){
                                       $sem_pass_status = 'CARRY'; 
                                       $carry_exams[] = $res->moduleAssignment->module->code;
                                       break;
                                   } 
   
                                   if($res->final_exam_remark == 'REPEAT'){
                                      $sem_pass_status = 'REPEAT'; 
                                      break;
                                   }
   
                                   if($res->final_exam_remark == 'FAIL'){
                                       $sem_pass_status = 'SUPP'; 
                                       $supp_exams[] = $res->moduleAssignment->module->code;
                                   }   
                                }
                              }
                            }
             
   
   
                           $remark = SemesterRemark::find($rem->id);
                           $remark->study_academic_year_id = $request->get('study_academic_year_id');
                           $remark->student_id = $key;
                           $remark->remark = ($stud_buffer[$key]['opt_prog_status'])? $sem_pass_status : 'INCOMPLETE';
                           if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                                $remark->gpa = null;
                           }else{
                              $remark->gpa = Util::computeGPA($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                           }
                           $remark->point = Util::computeGPAPoints($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                           $remark->credit = $stud_buffer[$key]['total_credit'];
                           $remark->year_of_study = $buffer['year_of_study'];
                           $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                           $remark->save();
                        }  
   
                         $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();
   
   
                         
                          if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->first()){
                             $remark = $rm;
                             $remark->student_id = $key;
                             $remark->year_of_study = $buffer['year_of_study'];
                             $remark->study_academic_year_id = $ac_yr_id;
                             $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
                             if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                                $remark->gpa = null;
                             }else{
                                  $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                                  if($remark->gpa < 2.0){
                                      $remark->remark = 'FAIL&DISCO';
                                  }
                                  $remark->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                                  $remark->credit = $buffer['annual_credit'];
                             } 
                             $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                             if($remark->gpa && $gpa_class){
                               $remark->class = $gpa_class->name;
                             }else{
                               $remark->class = null;
                             }
                             $remark->save();
   
                             $status = AcademicStatus::where('name',$remark->remark)->first();
   
                             $stud = Student::find($key);
                             $stud->academic_status_id = $status->id;
                             $stud->save();
                           
                          }
   
                          if($student_buffer[$key]['year_of_study'] == $student->year_of_study){
                         
                                 $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                                 $results = ExaminationResult::where('student_id',$key)->get();
                                 $points = 0;
                                 $credits = 0;
   
                                 foreach($results as $rs){
                                      if(!is_null($rs->point)){
                                         $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                                         $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                                      }   
                                 }
                                 
                                 $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                                 $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                                 // if(!$gpa_class){
                                  //  return redirect()->back()->with('error','GPA classification not defined');
                                 // }
                                 if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study){
                                    $overall_remark = $gpa_class->name;
   
                                    if($rm = OverallRemark::where('student_id',$key)->first()){
                                       $remark = $rm;
                                    }else{
                                       $remark = new OverallRemark;
                                    }
                                    $remark->student_id = $key;
                                    $remark->point = $points;
                                    $remark->credit = $credits;
                                    $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                                    
                                    if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                                       $remark->remark = null;
                                       $remark->class = null;
                                    }else{
                                       $remark->remark = Util::getOverallRemark($sem_remarks);
                                       $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                                    }
                                    $remark->save();
                                 }
                               }
                       }
   
                       if($student_buffer[$key]['year_of_study'] == $student->year_of_study && str_contains($semester->name,2)){
                         
                         $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                         $results = ExaminationResult::where('student_id',$key)->get();
                         $points = 0;
                         $credits = 0;
   
                         foreach($results as $rs){
                              if(!is_null($rs->point)){
                                 $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                                 $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                              }   
                         }
                         
                         $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                         $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                         // if(!$gpa_class){
                          //  return redirect()->back()->with('error','GPA classification not defined');
                         // }
                         if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study && str_contains($semester->name,2)){
                            $overall_remark = $gpa_class->name;
   
                            if($rm = OverallRemark::where('student_id',$key)->first()){
                               $remark = $rm;
                            }else{
                               $remark = new OverallRemark;
                            }
                            $remark->student_id = $key;
                            $remark->point = $points;
                            $remark->credit = $credits;
                            $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                            
                            if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                               $remark->remark = null;
                              $remark->class = null;
                            }else{
                               $remark->remark = Util::getOverallRemark($sem_remarks);
                               $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                            }
                            $remark->save();
                         }
                       }
                  }
   
           DB::commit();

           return redirect()->to('academic/results/'.$student->id.'/'.$ac_yr_id.'/'.$yr_of_study.'/show-student-results')->with('message','Results processed successfully');


      }catch(\Exception $e){
         return $e->getMessage();
         return redirect()->back()->with('error','Unable to get the resource specified in this request');
      }
    }

    /**
     * Store examination results
     */
    public function store(Request $request)
    {
        try{
            $validation = Validator::make($request->all(),[
                'final_score'=>'numeric|min:0|max:100',
            ]);

            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }

            DB::beginTransaction();
            $module_assignment = ModuleAssignment::with(['module','studyAcademicYear.academicYear','programModuleAssignment.campusProgram.program'])->find($request->get('module_assignment_id'));
              $academicYear = $module_assignment->studyAcademicYear->academicYear;

            $module = Module::with('ntaLevel')->find($module_assignment->module_id);
            // $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
            // if(!$policy){
            //       return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
            // }

            $student = Student::with('options')->find($request->get('student_id'));
            $elective_policy = ElectivePolicy::where('campus_program_id',$student->campus_program_id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->first();
            if(DB::table('student_program_module_assignment')->where('student_id',$student->id)->count() >= $elective_policy->number_of_options && $module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                return redirect()->back()->with('error','Number of options in elective policy has reached maximum limit');
            }

            if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                if(DB::table('student_program_module_assignment')->where('student_id',$student->id)->where('program_module_assignment_id',$module_assignment->program_module_assignment_id)->count() == 0){
                    $student->options()->attach([$module_assignment->program_module_assignment_id]);
                }
            }

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
                $result->final_score = ($request->get('final_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
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
                   $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                }
                if($result->supp_score){
                   $result->final_exam_remark = $$module_assignment->programModuleAssignment->module_pass_score <= $result->supp_score? 'PASS' : 'FAIL';
                }
                if(!$result->course_work_score){
                   $result->course_work_remark = 'INCOMPLETE';
                   $result->final_exam_remark = 'INCOMPLETE';
                }
                $result->final_uploaded_at = now();
                $result->uploaded_by_user_id = Auth::user()->id;
                $result->save();
                DB::commit();

                if($request->get('supp_score')){
                    $process_type = 'SUPP';
                }else{
                    $process_type = null;
                }

                $this->processStudentResults($request, null, $student->id,$module_assignment->study_academic_year_id,$module_assignment->programModuleAssignment->year_of_study, $process_type);

                return redirect()->to('academic/results/'.$student->id.'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->id.'/edit-student-results')->with('message','Results added successfully');

        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request'); 
        }
    }

    /**
     * Update examination results
     */
    public function update(Request $request)
    {
        try{
            $validation = Validator::make($request->all(),[
                'final_score'=>'numeric|min:0|max:100',
                'supp_score'=>'min:0|max:100',
            ]);



            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }

            DB::beginTransaction();
            $module_assignment = ModuleAssignment::with(['module','studyAcademicYear.academicYear','programModuleAssignment.campusProgram.program'])->find($request->get('module_assignment_id'));
              $academicYear = $module_assignment->studyAcademicYear->academicYear;

            $module = Module::with('ntaLevel')->find($module_assignment->module_id);
            // $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
            // if(!$policy){
            //       return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
            // }

            $student = Student::find($request->get('student_id'));
            $studentship_status = DB::table('studentship_statuses')
            ->select('name')
            ->where('id', '=', $student->studentship_status_id)
            ->get();

            // $student = Student::whereHas('studentshipStatus', function($query) use($request) {
            //    $query->where('student_id',$request->get('student_id'));
            // });

            // $student = Student::whereHas('studentshipStatus')
            // ->where('id',$request->get('student_id'));

            // $student = DB::table('students')
            // ->join('studentship_statuses', 'students.studentship_status_id', '=', 'studentship_statuses.id')
            // ->where('students.id', '=', $request->get('student_id'))
            // ->get();

            $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type',$request->get('exam_type'))->where('status','APPROVED')->first();

            $retake_history = RetakeHistory::whereHas('moduleAssignment',function($query) use($module){
                  $query->where('module_id',$module->id);
            })->where('student_id',$student->id)->first();

            $carry_history = CarryHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

            if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))
            ->where('student_id',$request->get('student_id'))
            ->where('exam_type',$request->get('exam_type'))
            ->first()){
                  $result = $res;
                  $result->module_assignment_id = $request->get('module_assignment_id');
                  $result->student_id = $request->get('student_id');
                  if($request->has('final_score')){
                  $result->course_work_score = $request->get('course_work_score');
                  $score_before = $result->final_score;
                  
                     if ($studentship_status[0]->name == 'GRADUANT' || $studentship_status[0]->name == 'DECEASED') {
                        return redirect()->back()->with('error','Unable to update deceased or graduant student results'); 
                     } else {
                        $result->final_score = ($request->get('final_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                     }

                  }else{
                     $result->final_score = null;
                  }
                  if($request->get('appeal_score')){
                     $result->appeal_score = ($request->get('appeal_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                  }
                  if($request->get('appeal_supp_score')){
                     $result->appeal_supp_score = $request->get('appeal_supp_score');
                  }
                  if($request->get('supp_score')){
                     $result->exam_type = 'SUPP';
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
                     $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                  }
                  if($result->supp_score && $result->retakable_type == 'carry_history'){
                     $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'REPEAT';
                  } else if ($result->supp_score && $result->retakable_type == 'retake_history') {
                     $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'RETAKE';
                  } else if ($result->supp_score) {
                     $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                  }
                  $result->final_uploaded_at = now();
                  $result->uploaded_by_user_id = Auth::user()->id;
                  $result->save();
                  
                  if(!Auth::user()->hasRole('hod')){
                      $change = new ExaminationResultChange;
                      $change->resultable_id = $result->id;
                      $change->from_score = $score_before;
                      $change->to_score = $result->final_score;
                      $change->resultable_type = 'examination_result';
                      $change->user_id = Auth::user()->id;
                      $change->save();
                  }
              }else{
                  $result = new ExaminationResult;
                  $result->module_assignment_id = $request->get('module_assignment_id');
                  $result->student_id = $request->get('student_id');
                  if($request->has('final_score')){
                  $result->course_work_score = $request->get('course_work_score');

                  if ($studentship_status[0]->name == 'GRADUANT' || $studentship_status[0]->name == 'DECEASED') {
                     return redirect()->back()->with('error','Unable to update deceased or graduant student results'); 
                  } else {
                     $result->final_score = ($request->get('final_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                  }

                  }else{
                     $result->final_score = null;
                  }
                if($request->get('appeal_score')){
                   $result->appeal_score = ($request->get('appeal_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                }
                if($request->get('appeal_supp_score')){
                   $result->appeal_supp_score = $request->get('appeal_supp_score');
                }
                if($request->get('supp_score')){
                   $result->exam_type = 'SUPP';
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
                   $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                }
                if($result->supp_score){
                   $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                }
                $result->final_uploaded_at = now();
                $result->uploaded_by_user_id = Auth::user()->id;
                $result->save();
              }
              
                DB::commit();

                session(['module_code_id' => $request->get('module_assignment_id')]);

                // return $this->processStudentResults($request,$student->id,$module_assignment->study_academic_year_id,$module_assignment->programModuleAssignment->year_of_study);
               //  $request->get('module_assignment_id').'/'.
                if($request->get('supp_score')){
                    return redirect()->to('academic/results/'.$request->get('student_id').'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$module_assignment->programModuleAssignment->semester_id.'&process_type=SUPP');
                }else{
                    return redirect()->to('academic/results/'.$request->get('student_id').'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$module_assignment->programModuleAssignment->semester_id);
                }
               

        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request'); 
        }
    }


    /**
     * Update examination results
     */
    public function updateAppeal(Request $request)
    {
        try{
            $validation = Validator::make($request->all(),[
                'final_score'=>'numeric|min:0|max:100',
                'supp_score'=>'min:0|max:100',
            ]);

            if($validation->fails()){
               if($request->ajax()){
                  return response()->json(array('error_messages'=>$validation->messages()));
               }else{
                  return redirect()->back()->withInput()->withErrors($validation->messages());
               }
            }

            DB::beginTransaction();
            $module_assignment = ModuleAssignment::with(['module','studyAcademicYear.academicYear','programModuleAssignment.campusProgram.program'])->find($request->get('module_assignment_id'));
              $academicYear = $module_assignment->studyAcademicYear->academicYear;

            $module = Module::with('ntaLevel')->find($module_assignment->module_id);
            // $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
            // if(!$policy){
            //       return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
            // }

            $student = Student::find($request->get('student_id'));

            $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type',$request->get('exam_type'))->first();

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
                $result->final_score = ($request->get('final_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                }else{
                   $result->final_score = null;
                }
                if($request->get('appeal_score')){
                   $result->appeal_score = ($request->get('appeal_score')*$module_assignment->programModuleAssignment->final_min_mark)/100;
                }
                if($request->get('appeal_supp_score')){
                   $result->appeal_supp_score = $request->get('appeal_supp_score');
                }
                if($request->get('supp_score')){
                   $result->exam_type = 'SUPP';
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
                   $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                }
                if($result->supp_score){
                   $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_score <= $result->supp_score? 'PASS' : 'FAIL';
                }
                $result->exam_type = 'APPEAL';
                $result->final_uploaded_at = now();
                $result->uploaded_by_user_id = Auth::user()->id;
                $result->save();

                $appeal = Appeal::find($request->get('appeal_id'));
                $appeal->is_attended = 1;
                $appeal->save();
                DB::commit();

                // return $this->processStudentResults($request,$student->id,$module_assignment->study_academic_year_id,$module_assignment->programModuleAssignment->year_of_study);

               // return redirect()->to('academic/results/'.$request->get('student_id').'/'.$module_assignment->study_academic_year_id.'/'.$module_assignment->programModuleAssignment->year_of_study.'/process-student-results?semester_id='.$module_assignment->programModuleAssignment->semester_id);
                  return redirect()->back()->with('message','Appeal Results updated successfully');

        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request'); 
        }
    }

    /**
     * Process student results
     */
    public function processStudentResults(Request $request, $student_id, $ac_yr_id,$yr_of_study, $process_type = null)
    {         
         
         try{
            DB::beginTransaction();
            $student = Student::findOrFail($student_id);
            $campus_program = CampusProgram::with(['program.ntaLevel'])->find($student->campus_program_id);
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
                if($assign->programModuleAssignment->category == 'COMPULSORY'){
                  if($assign->course_work_process_status != 'PROCESSED' && $assign->module->course_work_based == 1){
                    DB::rollback();
                    return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
                  }
                  if($assign->final_upload_status == null){
                    DB::rollback();
                    return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
                  }
                }else{
                  $exam_student_count = ProgramModuleAssignment::find($assign->program_module_assignment_id)->optedStudents()->count();
                  if($assign->course_work_process_status != 'PROCESSED' && $exam_student_count != 0 && $assign->module->course_work_based == 1){
                    DB::rollback();
                    return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
                  }
                  if($assign->final_upload_status == null && $exam_student_count != 0){
                    DB::rollback();
                    return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' final not uploaded');
                  }
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
            
              // if(!$policy){
              //    return redirect()->back()->with('error','Some programmes are missing examination policy');
              // }

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
                  return redirect()->back()->with('error','Some modules are missing final marks ('.implode(',', $missing_programs).')');
              }

              $elective_policy = ElectivePolicy::where('campus_program_id',$campus_program->id)->where('study_academic_year_id',$ac_yr_id)->where('semester_id',$semester->id)->first();

              if($elective_policy){
                if(ExaminationResult::whereHas('moduleAssignment',function($query){
                       $query->where('final_upload_status','UPLOADED');
                  })->whereHas('moduleAssignment.programModuleAssignment',function($query) use($campus_program){
                       $query->where('campus_program_id',$campus_program->id)->where('category','OPTIONAL');
                  })->where('student_id',$student->id)->distinct()->count('module_assignment_id') < $elective_policy->number_of_options){
                    DB::rollback();
                    return redirect()->back()->with('error','Some optional modules are missing final marks');
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
            

            $student_buffer[$student->id]['opt_credit'] = 0;
            $student_buffer[$student->id]['opt_prog'] = 0;
            $student_buffer[$student->id]['opt_prog_status'] = true;
            

            foreach($results as $key=>$result){   
               
                    $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                      $query->where('student_id',$student->id);
                        })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$request->get('semester_id'))->where('category','OPTIONAL')->get();

                   foreach($optional_programs as $prog){
                       $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                       $student_buffer[$student->id]['opt_prog'] += 1;
                   }
					if($elective_policy){
						if($student_buffer[$student->id]['opt_prog'] < $elective_policy->number_of_options){
						$student_buffer[$student->id]['opt_prog_status'] = false;
						}	
					}

                   $student_buffer[$student->id]['total_credit'] = $student_buffer[$student->id]['opt_credit'] + $total_credit;
                   
                  
                    if($result->retakeHistory && isset($result->retakeHistory->retakableResults[0])){
                        $processed_result = ExaminationResult::find($result->retakeHistory->retakableResults[0]->id);
                        
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
 

                    if($processed_result->appeal_score){
                      $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($processed_result->appeal_score))->where('max_score','>=',round($processed_result->appeal_score))->first();
                    }
      
                    if(!$grading_policy){
                       // DB::rollback();
                       return redirect()->back()->with('error','Some programmes NTA level are missing grading policies');
                    }
                    
                    if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                        $processed_result->grade = null;
                        $processed_result->point = null;
                      if($processed_result->final_remark == 'INCOMPLETE' || $processed_result->final_remark == 'POSTPONED'){
                          $processed_result->final_exam_remark = $processed_result->final_remark;
                      }
                      if($processed_result->course_work_remark == 'INCOMPLETE' || $processed_result->course_work_remark == 'POSTPONED'){
                          $processed_result->final_exam_remark = $processed_result->course_work_remark;
                      }
                    } else {

                        $processed_result->grade = $grading_policy? $grading_policy->grade : null;
                        $processed_result->point = $grading_policy? $grading_policy->point : null;

                        if($processed_result->course_work_remark == 'FAIL' || $processed_result->final_remark == 'FAIL'){

                           if($processed_result->supp_processed_at && $processed_result->final_exam_remark == 'CARRY') {
                              $processed_result->final_exam_remark = 'CARRY';
                              $processed_result->grade = 'F';
                              $processed_result->point = 0;
                           }elseif($processed_result->supp_processed_at && $processed_result->final_exam_remark == 'RETAKE'){
                              $processed_result->final_exam_remark = 'RETAKE';
                              $processed_result->grade = 'F';
                              $processed_result->point = 0;
                           } elseif($processed_result->supp_processed_at && $processed_result->final_exam_remark == 'REPEAT'){
                              $processed_result->final_exam_remark = 'REPEAT';
                              $processed_result->grade = 'F';
                              $processed_result->point = 0;
                           
                           } elseif ($processed_result->supp_processed_at) { 
                              $processed_result->final_exam_remark = 'PASS';
                              $processed_result->grade = 'C';
                              $processed_result->point = 1;
                           } else  {
                              $processed_result->final_exam_remark = 'FAIL';
                              $processed_result->grade = 'F';
                              $processed_result->point = 0;
                           }

                           // $processed_result->final_exam_remark = 'FAIL';
                           // $processed_result->grade = 'F';
                           // $processed_result->point = 0;

                        }else{
                          $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->total_score? 'PASS' : 'FAIL';
                        }

                        if($processed_result->supp_score){
                          if(Util::stripSpacesUpper($assignment->module->ntaLevel->name) == Util::stripSpacesUpper('NTA Level 7')){
                                  if($assignment->programModuleAssignment->year_of_study == 1){
                                       if($processed_result->retakable_id != null){

                                          if ($assignment->id == session('module_code_id')) {
                                             $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                          }

                                             // $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                          // if ($assignment->id == $processed_result->carryHistory->module_assignment_id) {
                                          //    $processed_result->final_exam_remark = 'CARRY';
                                          //    # code...
                                          // } else {
                                          //    $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                          // }
                                       } else {
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'CARRY';
                                       }
                                  } else {
                                       if($processed_result->retakable_id != null){
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                       }else{
                                            $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                                       }
                                  }
                                  
                            } else {

                                  if($processed_result->retakable_id != null){

                                    if ($assignment->id == session('module_code_id')) {
                                       $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'REPEAT';
                                    }

                                  } else {
                                      $processed_result->final_exam_remark = $assignment->programModuleAssignment->module_pass_mark <= $processed_result->supp_score? 'PASS' : 'RETAKE';
                                  }
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
                    // $student_buffer[$student->id]['total_credit'] = $total_credit;
                    $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;

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
            $annual_results = ExaminationResult::with(['moduleAssignment.module'])->where('module_assignment_id',$assign->id)->where('student_id',$student->id)->get();

               // check if semester one and two are processed

            $published_dataCount = DB::table('results_publications')
            ->where('study_academic_year_id', $assign->study_academic_year_id)
            ->where('nta_level_id', $campus_program->program->ntaLevel->id)
            ->whereIn('status', ['UNPUBLISHED','PUBLISHED'])
            ->count();

            if($published_dataCount > 1){
              $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
            }else{
              $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();
            }

            
      
          $annual_credit = 0;
          $student_buffer[$student->id]['opt_credit'] = 0;
          foreach($core_programs as $prog){            
                $annual_credit += $prog->module->credit;
          }

          
          
          

          foreach($annual_results as $key=>$result){
                if($published_dataCount > 1){
                        $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                           $query->where('student_id',$student->id);
                           })->with(['module'])->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                  
                }else {
                  $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($student){
                     $query->where('student_id',$student->id);
                       })->with(['module'])->where('semester_id', $semester->id)->where('study_academic_year_id',$assign->study_academic_year_id)->where('year_of_study',$assign->programModuleAssignment->year_of_study)->where('category','OPTIONAL')->get();
                }


                  
                
               if(!isset($student_buffer[$student->id]['results'])){
                    $student_buffer[$student->id]['results'] = [];
                    $student_buffer[$student->id]['total_credit'] = 0;
                 }
                $student_buffer[$student->id]['nta_level'] = $campus_program->program->ntaLevel;
               $student_buffer[$student->id]['annual_results'][] =  $result;
               $student_buffer[$student->id]['year_of_study'] = $yr_of_study;
               $student_buffer[$student->id]['annual_credit'] = $annual_credit;
               foreach($optional_programs as $prog){
                   $student_buffer[$student->id]['opt_credit'] += $prog->module->credit;
                   $student_buffer[$student->id]['annual_credit'] = $student_buffer[$student->id]['opt_credit'] + $annual_credit;
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

                  if($res->final_exam_remark == 'REPEAT'){
                      $pass_status = 'REPEAT'; 
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
                $remark->remark = ($buffer['opt_prog_status'])? $pass_status : 'INCOMPLETE';
                if($remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                     $remark->gpa = null;
                }else{
                   $remark->gpa = Util::computeGPA($buffer['total_credit'],$buffer['results']);
                   $remark->point = Util::computeGPAPoints($buffer['total_credit'],$buffer['results']);
                   $remark->credit = $buffer['total_credit'];
                }
                $remark->year_of_study = $buffer['year_of_study'];
                $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                if($remark->gpa && $gpa_class){
                  $remark->class = $gpa_class->name;
                }else{
                  $remark->class = null;
                }
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
                    if($rem->remark == 'INCOMPLETE' || $rem->remark == 'INCOMPLETE' || $rem->remark == 'POSTPONED' || $rem->remark == 'SUPP'){
                       $rem->gpa = null;
                    }else{
                         $rem->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                         if($rem->gpa < 2.0){
                            $rem->remark = 'FAIL&DISCO';
                         }
                         $rem->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                         $rem->credit = $buffer['annual_credit'];
                    }

                  //   return Util::computeGPA($buffer['annual_credit'],$buffer['annual_results'])."<br><br>".$buffer['annual_credit']."<br><br>".Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results'])."<br><br>".implode(", ", $buffer['annual_results']);

                    if($sem_remarks[0]->remark == 'POSTPONED' && $sem_remarks[(count($sem_remarks)-1)]->remark != 'POSTPONED'){
                               $rem->remark = $sem_remarks[(count($sem_remarks)-1)]->remark;
                            }
                    $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($rem->gpa,1,1))->where('max_gpa','>=',bcdiv($rem->gpa,1,1))->first();
                    if($rem->gpa && $gpa_class){
                      $rem->class = $gpa_class->name;
                    }else{
                      $rem->class = null;
                    }
                    $rem->save();

                    $status = AcademicStatus::where('name',$remark->remark)->first();

                    $stud = Student::find($key);
                    $stud->academic_status_id = $status->id;
                    $stud->save();

                    if($process_type == 'SUPP'){
                     $sem_remarks = SemesterRemark::with(['student'])->where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();



                     foreach ($sem_remarks as $rem) {
                        $mod_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request,$rem,$yr_of_study,$campus_program){
                            $query->where('campus_program_id',$campus_program->id)->where('year_of_study',$yr_of_study)->where('semester_id',$rem->semester_id);
                          })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                        $query->where('program_id',$campus_program->program->id);
                          })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$ac_yr_id)->get();

                        $stud_buffer = [];
                        $ann_credit = 0;

                        $elective_policy = ElectivePolicy::where('campus_program_id',$rem->student->campus_program_id)->where('study_academic_year_id',$rem->study_academic_year_id)->where('semester_id',$rem->semester_id)->first();

                        foreach ($mod_assignments as $assignment) {
                          $results = ExaminationResult::whereHas('student.applicant',function($query) use($request){
                                  $query->where('intake_id',$request->get('intake_id'));
                          })->with(['retakeHistory.retakableResults'=>function($query){
                                   $query->latest();
                                },'carryHistory.carrableResults'=>function($query){
                                   $query->latest();
                                }])->where('module_assignment_id',$assignment->id)->where('student_id',$key)->get();
                  
                            $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->where('campus_program_id',$assign->programModuleAssignment->campus_program_id)->get();

                          $tot_credit = 0;
                          
                          foreach($core_programs as $prog){
                            
                                 $ann_credit += $prog->module->credit;

                            if($prog->semester_id == $rem->semester_id){
                              $tot_credit += $prog->module->credit;
                            }
                          }

                            foreach($results as $resKey=>$result){
                            $std = Student::with(['campusProgram.program.ntaLevel'])->find($result->student_id);
                                  
                            
                                  $optional_programs = ProgramModuleAssignment::whereHas('optedStudents',function($query) use($std){
                                    $query->where('student_id',$std->id);
                                      })->with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$rem->semester_id)->where('category','OPTIONAL')->get();
                                
                                 $stud_buffer[$key]['total_credit'] = $total_credit;
                                 $stud_buffer[$key]['opt_credit'] = 0;
                                 $stud_buffer[$key]['opt_prog_status'] = true;
                                 $stud_buffer[$key]['opt_prog'] = 0;
                                 $stud_buffer[$key]['results'][] = $result;

                                 foreach($optional_programs as $prog){
                                     $stud_buffer[$key]['opt_credit'] += $prog->module->credit;
                                     $stud_buffer[$key]['opt_prog'] += 1; 
                                 }
                                 if($stud_buffer[$key]['opt_prog_status'] < $elective_policy->number_of_options){
                                    $stud_buffer[$key]['opt_prog_status'] = false;
                                 }
                                 $stud_buffer[$key]['total_credit'] = $stud_buffer[$key]['opt_credit'] + $tot_credit;           

                            }
                        }

                        foreach($stud_buffer as $bufKey=>$buf){
                             $sem_pass_status = 'PASS';
                             $supp_exams = [];
                             $retake_exams = [];
                             $carry_exams = [];
                          if(isset($buf['results'])){
                             foreach($buf['results'] as $res){
                                if($res->final_exam_remark == 'INCOMPLETE'){
                                    $sem_pass_status = 'INCOMPLETE';
                                    break;
                                }

                                if($res->final_exam_remark == 'POSTPONED'){
                                    $sem_pass_status = 'POSTPONED';
                                    break;
                                }

                                if($res->final_exam_remark == 'RETAKE'){
                                    $sem_pass_status = 'RETAKE'; 
                                    $retake_exams[] = $res->moduleAssignment->module->code;
                                    break;
                                }  

                                if($res->final_exam_remark == 'CARRY'){
                                    $sem_pass_status = 'CARRY'; 
                                    $carry_exams[] = $res->moduleAssignment->module->code;
                                    break;
                                } 

                                if($res->final_exam_remark == 'REPEAT'){
                                   $sem_pass_status = 'REPEAT'; 
                                   break;
                                }

                                if($res->final_exam_remark == 'FAIL'){
                                    $sem_pass_status = 'SUPP'; 
                                    $supp_exams[] = $res->moduleAssignment->module->code;
                                }   
                             }
                           }
                         }
          


                        $remark = SemesterRemark::find($rem->id);
                        $remark->study_academic_year_id = $request->get('study_academic_year_id');
                        $remark->student_id = $key;
                        $remark->remark = ($stud_buffer[$key]['opt_prog_status'])? $sem_pass_status : 'INCOMPLETE';
                        if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                             $remark->gpa = null;
                        }else{
                           $remark->gpa = Util::computeGPA($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                        }
                        $remark->point = Util::computeGPAPoints($stud_buffer[$key]['total_credit'],$stud_buffer[$key]['results']);
                        $remark->credit = $stud_buffer[$key]['total_credit'];
                        $remark->year_of_study = $buffer['year_of_study'];
                        $remark->serialized = count($supp_exams) != 0? serialize(['supp_exams'=>$supp_exams,'carry_exams'=>$carry_exams,'retake_exams'=>$retake_exams]) : null;
                        $remark->save();
                     }  

                      $sem_remarks = SemesterRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->get();


                      
                       if($rm = AnnualRemark::where('student_id',$key)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$buffer['year_of_study'])->first()){
                          $remark = $rm;
                          $remark->student_id = $key;
                          $remark->year_of_study = $buffer['year_of_study'];
                          $remark->study_academic_year_id = $ac_yr_id;
                          $remark->remark = Util::getAnnualRemark($sem_remarks,$buffer['annual_results']);
                          if($remark->remark == 'INCOMPLETE' || $remark->remark == 'INCOMPLETE' || $remark->remark == 'POSTPONED' || $remark->remark == 'SUPP'){
                             $remark->gpa = null;
                          }else{
                               $remark->gpa = Util::computeGPA($buffer['annual_credit'],$buffer['annual_results']);
                               if($remark->gpa < 2.0){
                                   $remark->remark = 'FAIL&DISCO';
                               }
                               $remark->point = Util::computeGPAPoints($buffer['annual_credit'],$buffer['annual_results']);
                               $remark->credit = $buffer['annual_credit'];
                          } 
                          $gpa_class = GPAClassification::where('nta_level_id',$buffer['nta_level']->id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($remark->gpa,1,1))->where('max_gpa','>=',bcdiv($remark->gpa,1,1))->first();
                          if($remark->gpa && $gpa_class){
                            $remark->class = $gpa_class->name;
                          }else{
                            $remark->class = null;
                          }
                          $remark->save();

                          $status = AcademicStatus::where('name',$remark->remark)->first();

                          $stud = Student::find($key);
                          $stud->academic_status_id = $status->id;
                          $stud->save();
                        
                       }

                       if($student_buffer[$key]['year_of_study'] == $student->year_of_study){
                      
                              $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                              $results = ExaminationResult::where('student_id',$key)->get();
                              $points = 0;
                              $credits = 0;

                              foreach($results as $rs){
                                   if(!is_null($rs->point)){
                                      $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                                      $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                                   }   
                              }
                              
                              $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                              $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                              // if(!$gpa_class){
                               //  return redirect()->back()->with('error','GPA classification not defined');
                              // }
                              if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study){
                                 $overall_remark = $gpa_class->name;

                                 if($rm = OverallRemark::where('student_id',$key)->first()){
                                    $remark = $rm;
                                 }else{
                                    $remark = new OverallRemark;
                                 }
                                 $remark->student_id = $key;
                                 $remark->point = $points;
                                 $remark->credit = $credits;
                                 $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                                 
                                 if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                                    $remark->remark = null;
                                    $remark->class = null;
                                 }else{
                                    $remark->remark = Util::getOverallRemark($sem_remarks);
                                    $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                                 }
                                 $remark->save();
                              }
                            }
                    }

                    if($student_buffer[$key]['year_of_study'] == $student->year_of_study && str_contains($semester->name,2)){
                      
                      $sem_remarks = SemesterRemark::where('student_id',$key)->get();
                      $results = ExaminationResult::where('student_id',$key)->get();
                      $points = 0;
                      $credits = 0;

                      foreach($results as $rs){
                           if(!is_null($rs->point)){
                              $points += ($rs->point*$rs->moduleAssignment->programModuleAssignment->module->credit);
                              $credits += $rs->moduleAssignment->programModuleAssignment->module->credit;
                           }   
                      }
                      
                      $overall_gpa = $credits != 0? bcdiv($points/$credits, 1,1) : null;
                      $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
                      // if(!$gpa_class){
                       //  return redirect()->back()->with('error','GPA classification not defined');
                      // }
                      if($gpa_class && $student_buffer[$key]['year_of_study'] == $student->year_of_study && str_contains($semester->name,2)){
                         $overall_remark = $gpa_class->name;

                         if($rm = OverallRemark::where('student_id',$key)->first()){
                            $remark = $rm;
                         }else{
                            $remark = new OverallRemark;
                         }
                         $remark->student_id = $key;
                         $remark->point = $points;
                         $remark->credit = $credits;
                         $remark->gpa = Util::getOverallRemark($sem_remarks) != 'POSTPONED' || Util::getOverallRemark($sem_remarks) != 'INCOMPLETE'? $overall_gpa : null;
                         
                         if(Util::getOverallRemark($sem_remarks) == 'POSTPONED'){
                            $remark->remark = null;
                           $remark->class = null;
                         }else{
                            $remark->remark = Util::getOverallRemark($sem_remarks);
                            $remark->class = Util::getOverallRemark($sem_remarks) == 'PASS' || Util::getOverallRemark($sem_remarks) == 'CARRY' || Util::getOverallRemark($sem_remarks) == 'RETAKE' || Util::getOverallRemark($sem_remarks) == 'SUPP'? $overall_remark : null;
                         }
                         $remark->save();
                      }
                    }
               }

           DB::commit();

           return redirect()->to('academic/results/'.$student->id.'/'.$ac_yr_id.'/'.$yr_of_study.'/show-student-results')->with('message','Results processed successfully');
        }catch(\Exception $e){
           return $e->getMessage();
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
            'intakes'=>Intake::all(),
            'staff'=>User::find(Auth::user()->id)->staff,
            'request'=>$request
    	];

    	return view('dashboard.academic.program-results',$data)->withTitle('Final Results');
    }

    /**
     * Display results report
     */
    public function showProgramResultsReport(Request $request)
    {
      $campus_program = CampusProgram::with(['program.departments','campus'])->find(explode('_',$request->get('campus_program_id'))[0]);
        $study_academic_year = StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id'));
      $semester = Semester::find($request->get('semester_id'));
      foreach($campus_program->program->departments as $dpt){
         if($dpt->pivot->campus_id == $campus_program->campus_id){
            $department = $dpt;
         }
      }
    	if($request->get('semester_id') != 'SUPPLEMENTARY'){
	    	$module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
	                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
	    	        })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$campus_program->program->id);
	    	        })->with(['module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

                if(ModuleAssignment::whereHas('examinationResults',function($query){
                     $query->whereNull('final_processed_at');
                })->whereHas('programModuleAssignment',function($query) use($request){
                     $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('semester_id',$request->get('semester_id'));
                })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                     $query->where('program_id',$campus_program->program->id);
                })->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
                   return redirect()->back()->with('error','Results not processed');
                }

        }else{
        	$module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($request){
                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
    	         })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$campus_program->program->id);
	    	    })->with(['module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

               if(ModuleAssignment::whereHas('examinationResults',function($query){
                     $query->whereNull('final_processed_at');
               })->whereHas('programModuleAssignment',function($query) use($request){
                $query->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
               })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
                     $query->where('program_id',$campus_program->program->id);
               })->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
                   return redirect()->back()->with('error','Results not processed');
               }
        }
        
        // Extract module assignments IDs
        $assignmentIds = [];
        foreach($module_assignments as $assign){
        	$assignmentIds[] = $assign->id;
        }

        if($request->get('semester_id') != 'SUPPLEMENTARY'){
           if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 1')){
              $students = Student::whereHas('applicant',function($query) use($request){
                  $query->where('intake_id',$request->get('intake_id'));
              })->whereHas('registrations',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
               })->with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'semesterRemarks.semester','examinationResults'=>function($query) use($assignmentIds){
                $query->whereIn('module_assignment_id',$assignmentIds);
              },'examinationResults.changes'])->where('campus_program_id',$campus_program->id)->get();
           }else{
              $students = Student::whereHas('applicant',function($query) use($request){
                  $query->where('intake_id',$request->get('intake_id'));
              })->whereHas('registrations',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              })->with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'semesterRemarks.semester','annualRemarks'=>function($query) use($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'examinationResults'=>function($query) use($assignmentIds){
              	$query->whereIn('module_assignment_id',$assignmentIds);
              },'examinationResults.changes'])->where('campus_program_id',$campus_program->id)->get();
          }
        }else{
/* 			whereHas('studentshipStatus',function($query){
                  $query->where('name','ACTIVE')->orWhere('name','RESUMED')->orWhere('name','GRADUATING');
              })-> */
            $students = Student::whereHas('applicant',function($query) use($request){
                  $query->where('intake_id',$request->get('intake_id'));
              })->whereHas('registrations',function($query) use($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
            })->whereHas('annualRemarks',function($query) use($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              })->with(['semesterRemarks'=>function($query) use ($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'semesterRemarks.semester','annualRemarks'=>function($query) use($request){
                   $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2]);
              },'examinationResults'=>function($query) use($assignmentIds){
                $query->whereIn('module_assignment_id',$assignmentIds);
              },'specialExams','examinationResults.changes','examinationResults.moduleAssignment.specialExams'])->where('campus_program_id',$campus_program->id)->get();
        }


        if(count($students) != 0){
           if(count($students[0]->examinationResults) == 0){
              return redirect()->back()->with('error','No results processed yet for this programme');
           }
        }
        $grading_policies = GradingPolicy::where('nta_level_id',$campus_program->program->nta_level_id)
        ->where('study_academic_year_id',$request->get('study_academic_year_id'))
        ->orderBy('max_score')
        ->get();

        $modules = [];

        foreach($module_assignments as $assignment){
              $modules[$assignment->module->code] = [];
              $modules[$assignment->module->code]['semester_id'] = $assignment->programModuleAssignment->semester_id; 
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
              $modules[$assignment->module->code]['grades_perc'][$policy->grade] = 0;
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
                     $modules[$assignment->module->code]['semester_id'] = $assignment->programModuleAssignment->semester_id; 
                     if($student->gender == 'M'){
                                  
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
           'department'=>$department,
           'study_academic_year'=>$study_academic_year,
           'module_assignments'=>$module_assignments,
           'students'=>$students,
           'modules'=>$modules,
           'semester'=>$semester,
           'intake'=>Intake::find($request->get('intake_id')),
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
     * Display global report form
     */
    public function showGlobalReport(Request $request)
    {
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get()
        ];
        return view('dashboard.academic.show-global-report',$data)->withTitle('Global Report');
    }


    /**
     * Display global report
     */
    public function getGlobalReport(Request $request)
    {
        $report = [];
        $departments = Department::with(['programs.ntaLevel'])->get();
        $nta_levels = NTALevel::all();
        foreach($nta_levels as $level){
            foreach($departments as $department){
                $report[$level->name]['departments'][] = $department;
                $report[$level->name][$department->name]['programs'] = [];

                foreach($department->programs as $program){
                   if($program->nta_level_id == $level->id){
                      $report[$level->name][$department->name]['programs'][] = $program;
                      $report[$level->name][$department->name][$program->name]['total_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['take_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['miss_take_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['post_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['inc_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['pass_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['fail_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['pass_students_rate'] = 0;
                      $report[$level->name][$department->name][$program->name]['fail_students_rate'] = 0;
                      $report[$level->name][$department->name][$program->name]['take_students_rate'] = 0;
                      $report[$level->name][$department->name][$program->name]['miss_take_students_rate'] = 0;
                      $report[$level->name][$department->name][$program->name]['ML']['take_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['FL']['take_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['ML']['post_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['FL']['post_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['ML']['inc_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['FL']['inc_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['ML']['pass_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['FL']['pass_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['ML']['fail_students'] = 0;
                      $report[$level->name][$department->name][$program->name]['FL']['fail_students'] = 0;
                   }
                   
                }
            }
        }

        $results = ExaminationResult::whereHas('moduleAssignment',function($query) use($request){
            $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
        })->with(['moduleAssignment.programModuleAssignment.module.ntaLevel','student'])->get();

        $module_assignments = ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->get();


        foreach($results as $key=>$result){
            foreach($module_assignments as $assignment){
               if($result->module_assignment_id == $assignment->id){
                  foreach($departments as $department){
                   // $report[$level->name]['departments'][] = $department->name;
                    foreach($department->programs as $program){
                        if($program->nta_level_id == $result->moduleAssignment->programModuleAssignment->module->nta_level_id){

                          $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'] += 1;
                          if($result->final_exam_remark == 'PASS' || $result->final_exam_remark == 'FAIL' || $result->final_exam_remark == 'RETAKE' || $result->final_exam_remark == 'CARRY'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['take_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['take_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['take_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];

                               if($result->student->gender == 'M'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['ML']['take_students'] += 1;
                               }

                               if($result->student->gender == 'F'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['FL']['take_students'] += 1;
                               }
                          }

                          if($result->final_exam_remark == 'PASS'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['pass_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['pass_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['pass_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];

                               if($result->student->gender == 'M'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['ML']['pass_students'] += 1;
                               }

                               if($result->student->gender == 'F'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['FL']['pass_students'] += 1;
                               }
                          }

                          if($result->final_exam_remark == 'FAIL' || $result->final_exam_remark == 'RETAKE' || $result->final_exam_remark == 'CARRY'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['fail_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['fail_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['fail_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];

                               if($result->student->gender == 'M'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['ML']['fail_students'] += 1;
                               }

                               if($result->student->gender == 'F'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['FL']['fail_students'] += 1;
                               }
                          }

                          if($result->final_exam_remark == 'POSTPONED'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['post_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['post_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['post_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];

                               if($result->student->gender == 'M'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['ML']['post_students'] += 1;
                               }

                               if($result->student->gender == 'F'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['FL']['post_students'] += 1;
                               }
                          }

                          if($result->final_exam_remark == 'INCOMPLETE'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['inc_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['inc_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['inc_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];

                               if($result->student->gender == 'M'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['ML']['inc_students'] += 1;
                               }

                               if($result->student->gender == 'F'){
                                  $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['FL']['inc_students'] += 1;
                               }
                          }

                          if($result->final_exam_remark == 'INCOMPLETE' || $result->final_exam_remark == 'POSTPONED'){
                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['miss_take_students'] += 1;

                               $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['miss_take_students_rate'] = $report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['miss_take_students']*100/$report[$result->moduleAssignment->programModuleAssignment->module->ntaLevel->name][$department->name][$program->name]['total_students'];
                          }
                     
                        }  
                    }
                  }
               }
            }
        }

        $data = [
            'report'=>$report,
            'study_academic_year'=>StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')),
            'nta_levels'=>$nta_levels,
            'departments'=>$departments
        ];

        return view('dashboard.academic.reports.global-report',$data)->withTitle('Global Report');

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
            'campus_programs'=>$request->has('campus_id')? CampusProgram::with(['program.departments'])->where('campus_id',$request->get('campus_id'))->get() : [],
            'modules'=>$request->has('campus_id')? Module::whereHas('moduleAssignments.programModuleAssignment.campusProgram',function($query) use ($request){
            	$query->where('campus_id',$request->get('campus_id'));
            })->get() : [],
            'staff'=>User::find(Auth::user()->id)->staff,
            'request'=>$request
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
    	    })->with(['programModuleAssignment.semester'])->where('module_id',$request->get('module_id'))->with('module.ntaLevel','programModuleAssignment.campusProgram.program.departments','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->first();

      $staff = User::find(Auth::user()->id)->staff;

    	if(!$module_assignment){
    		return redirect()->back()->with('error','No module assignment for selected academic year');
    	}

      if(Auth::user()->hasRole('staff') && !Auth::user()->hasRole('hod')){
          if($module_assignment->staff_id != $staff->id){
             return redirect()->back()->with('error','You are not allowed to view this module results');
          }
      }

    	$students = Student::whereHas('studentshipStatus',function($query){
          $query->where('name','ACTIVE');
      })->whereHas('examinationResults.moduleAssignment',function($query) use($request){
        	$query->where('module_id',$request->get('module_id'));
        })->with(['examinationResults.moduleAssignment'=>function($query) use($request){
        	$query->where('module_id',$request->get('module_id'));
        }])->get();

      if(count($students) != 0){
         if(count($students[0]->examinationResults) == 0){
             return redirect()->back()->with('error','No results processed yet');
         }
      }
      foreach($module_assignment->programModuleAssignment->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $module_assignment->programModuleAssignment->campusProgram->campus_id){
                    $department = $dpt;
                }
             }
    	$data = [
    		    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
            'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
            'department'=>$department,
            'module'=>$module_assignment->module,
            'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
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
    	$student = Student::whereHas('studentshipStatus',function($query){
           $query->where('name','ACTIVE');
      })->with(['campusProgram.program.departments'])->where('registration_number',$request->get('registration_number'))->first();
      $staff = User::find(Auth::user()->id)->staff;
      if(!$student){
          return redirect()->back()->with('error','No student found with searched registration number');
      }

      if(!$student->campusProgram){
         return redirect()->back()->with('error','Student not registered.');
      }

      if(Auth::user()->hasRole('examination-officer')){
          
          if($student->campusProgram->campus_id != $staff->campus_id){
             return redirect()->back()->with('error','Student not in the same campus.');
          }
      }
      
      if(!Auth::user()->hasRole('staff') && Auth::user()->hasRole('hod')){
        if(!Util::collectionContainsKey($student->campusProgram->program->departments, $staff->department_id)){
           return redirect()->back()->with('error','Student not in the same deprtment.');
        }
      }
      
      if(Auth::user()->hasRole('staff') && !Auth::user()->hasRole('hod')){
          if(ExaminationResult::whereHas('moduleAssignment',function($query) use($staff){
              $query->where('staff_id',$staff->id)->where('study_academic_year_id',session('active_academic_year_id'));
          })->where('student_id',$student->id)->count() == 0){
              return redirect()->back()->with('error','Unable to view student details because he/she is not one of your students in this academic year');
          }
      }

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
           'staff'=>$staff
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
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study){
         	 $query->where('student_id',$student->id)
             ->where('study_academic_year_id',$ac_yr_id)
             ->where('year_of_study',$yr_of_study);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student_id){
         	   $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment','moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
         	   $query->where('id',$student_id);
             })->with(['module'])
             ->where('study_academic_year_id',$ac_yr_id)
             ->where('year_of_study',$yr_of_study)
             ->where('category','OPTIONAL')
             ->get();

          $annual_remark = AnnualRemark::where('student_id',$student_id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         // 	$optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study){
                   $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY');
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study){
                     $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL');
                })->get();

              $moduleIds = [];
              foreach ($core_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }

              foreach ($opt_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }
              
              $missing_modules = [];
              foreach ($core_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }
              foreach ($opt_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }

         
              $num_options = DB::table('elective_policies')
              ->where('campus_program_id', $student->campus_program_id)
              ->where('study_academic_year_id', $ac_yr_id)
              ->where('semester_id', session('active_semester_id'))
              ->where('year_of_study', $yr_of_study)
              ->select('number_of_options')
              ->get();


              $opt = DB::table('program_module_assignments')
               ->join('student_program_module_assignment', 'program_module_assignments.id', '=', 'student_program_module_assignment.program_module_assignment_id')
               ->where('study_academic_year_id',$ac_yr_id)
               ->where('semester_id', session('active_semester_id'))
               ->where('campus_program_id',$student->campus_program_id)
               ->where('student_program_module_assignment.student_id', $student->id)
               ->where('year_of_study',$yr_of_study)
               ->count();
              
              $var_options = $num_options->pluck('number_of_options')->all();
             

              $supp_publish_status = DB::table('results_publications')
              ->where('study_academic_year_id', $ac_yr_id)
              ->where('nta_level_id', $student->campusProgram->program->nta_level_id)
              ->where('campus_id', $student->campusProgram->campus_id)
              ->where('status', 'PUBLISHED')
              ->where('type', 'SUPP')
              ->get();

         $data = [
         	'semesters'=>$semesters,
         	'annual_remark'=>$annual_remark,
         	'results'=>$results,
            'year_of_study'=>$yr_of_study,
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'optional_programs'=>$optional_programs,
            'missing_modules' => $missing_modules,
            'student'=>$student,
            'staff'=>User::find(Auth::user()->id)->staff,
            'num_options' => (sizeof($var_options) == 0) ? null: $var_options[0],
            'opt'     => $opt,
            'supp_publish_status' => $supp_publish_status
         ];
         return view('dashboard.academic.reports.final-student-annual-results',$data)->withTitle('Student Results');
    }

    /**
     * Display student overall results
     */
    public function showStudentOverallResults(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
         $student = Student::with(['campusProgram.program'])->find($student_id);
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study, $request){
           $query->where('student_id',$student->id)->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $request){
             //$query->where('study_academic_year_id',$ac_yr_id);
             $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });
         })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study, $request){
             $query->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });//->where('study_academic_year_id',$ac_yr_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study, $request){
           $query->where('year_of_study',$yr_of_study)->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           });//->where('study_academic_year_id',$ac_yr_id);
         },'moduleAssignment.specialExams'=>function($query) use($student){
            $query->where('student_id',$student->id);
         },'moduleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults'=>function($query){
            $query->latest();
         }])->where('student_id',$student->id)->get();
         
        // ->where('study_academic_year_id',$ac_yr_id)
       //  where('study_academic_year_id',$ac_yr_id)->
         $core_programs = ProgramModuleAssignment::with(['module'])->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
             $query->where('id',$student_id);
             })->with(['module'])->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
          //where('study_academic_year_id',$ac_yr_id)->
          $annual_remark = AnnualRemark::where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('student_id',$student_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         //   $optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study, $request){
                   $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
           })->where('year_of_study',$yr_of_study)->where('category','COMPULSORY');
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment.students',function($query) use($student){
                     $query->where('id',$student->id);
                 })->whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study, $request){
                     $query->where(function($query) use($ac_yr_id, $request){
               $query->where('study_academic_year_id',$ac_yr_id)->orWhere('study_academic_year_id',$request->get('next_ac_yr_id'));
               })->where('year_of_study',$yr_of_study)->where('category','OPTIONAL');
                })->get();

            $publications = ResultPublication::where('study_academic_year_id',$ac_yr_id)->where('status','PUBLISHED')->get();

              $moduleIds = [];
              foreach ($core_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }

              foreach ($opt_program_modules as $module) {
                foreach($results as $result){
                   if($result->module_assignment_id == $module->id){
                      $moduleIds[] = $module->id;
                   }
                }
              }
              
              $missing_modules = [];
              foreach ($core_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }
              foreach ($opt_program_modules as $module) {
                 if(!in_array($module->id, $moduleIds)){
                    $missing_modules[$module->programModuleAssignment->semester_id][] = $module;
                 }
              }

         $data = [
          'semesters'=>$semesters,
          'annual_remark'=>$annual_remark,
          'results'=>$results,
          'year_of_study'=>$yr_of_study,
          'study_academic_year'=>$study_academic_year,
          'core_programs'=>$core_programs,
          'optional_programs'=>$optional_programs,
          'missing_modules' => $missing_modules,
          'student'=>$student,
          'staff'=>User::find(Auth::user()->id)->staff
         ];
         return view('dashboard.academic.reports.final-student-overall-results',$data)->withTitle('Student Overall Results');
    }

    /**
     * Display student perfomance report
     */
    public function showStudentPerfomanceReport(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
         $student = Student::with(['campusProgram.program.departments','campusProgram.program.ntaLevel','campusProgram.campus','applicant'])->find($student_id);
         $study_academic_year = StudyAcademicYear::with('academicYear')->find($ac_yr_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student, $ac_yr_id, $yr_of_study){
           $query->where('student_id',$student->id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         }])->get();
         $results = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student_id){
             $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student_id);
         })->with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
           $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults.moduleAssignment.module','carryHistory.carryHistory.carrableResults.moduleAssignment.module'])->where('student_id',$student->id)->get();

         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('campus_program_id',$student->campus_program_id)->get();
         $optional_programs = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
             $query->where('id',$student_id);
             })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();

          $annual_remark = AnnualRemark::where('student_id',$student_id)->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->first();
         // if(count($optional_programs) == 0){
         //   $optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

         $core_program_modules = ModuleAssignment::whereHas('programModuleAssignment',function($query) use ($ac_yr_id,$yr_of_study){
                   $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY');
                 })->get();
            $opt_program_modules = ModuleAssignment::whereHas('programModuleAssignment.students',function($query) use($student){
                     $query->where('id',$student->id);
                 })->whereHas('programModuleAssignment',function($query) use($ac_yr_id,$yr_of_study){
                     $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL');
                })->get();

            $grading_policies = GradingPolicy::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->orderBy('min_score','DESC')->get();

            foreach($student->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                    $department = $dpt;
                }
             }

         $data = [
          'semesters'=>$semesters,
          'annual_remark'=>$annual_remark,
          'results'=>$results,
          'department'=>$department,
          'year_of_study'=>$yr_of_study,
          'study_academic_year'=>$study_academic_year,
          'core_programs'=>$core_programs,
          'optional_programs'=>$optional_programs,
          'student'=>$student,
          'grading_policies'=>$grading_policies,
          'staff'=>User::find(Auth::user()->id)->staff
         ];

         // $pdf = PDF::loadView('dashboard.academic.reports.perfomance-report', $data)->setPaper('a4','portrait');
         // return $pdf->stream();
         return view('dashboard.academic.reports.perfomance-report',$data)->withTitle('Student Perfomance Report');
    }

    /**
     * Display student perfomance report
     */
    public function showStudentStatementOfResults(Request $request, $student_id)
    {
         $student = Student::with(['campusProgram.program.departments','campusProgram.program.ntaLevel','campusProgram.campus','applicant'])->find($student_id);
         $semesters = Semester::with(['remarks'=>function($query) use ($student){
           $query->where('student_id',$student->id);
         }])->get();

         // $sems = ProgramModuleAssignment::whereHas('moduleAssignments.examinationResults',function($query) use($student){
         //      $query->where('student_id',$student->id);
         // })->whereHas('studyAcademicYear.semesterRemarks',function($query) use($student){
         //      $query->where('remark','!=','INCOMPLETE');
         // })->distinct()->groupBy(['year_of_study','semester_id','study_academic_year_id'])->orderBy('year_of_study')->get(['year_of_study','semester_id','study_academic_year_id']);


         $sems = DB::select("SELECT DISTINCT p.year_of_study,p.semester_id,p.study_academic_year_id FROM semester_remarks s JOIN program_module_assignments p ON s.study_academic_year_id = p.study_academic_year_id WHERE s.remark != 'INCOMPLETE' and s.semester_id = p.semester_id and s.student_id = ".$student->id." ORDER BY p.year_of_study ASC");


         $results = ExaminationResult::whereHas('moduleAssignment.studyAcademicYear.semesterRemarks',function($query) use($student){
              $query->where('remark','!=','INCOMPLETE');
         })->with(['moduleAssignment.programModuleAssignment','moduleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults'=>function($query){
            $query->latest();
         },'retakeHistory.retakableResults.moduleAssignment.module','carryHistory.carryHistory.carrableResults.moduleAssignment.module'])->where('student_id',$student->id)->get();

          $annual_remark = AnnualRemark::where('student_id',$student_id)->get();
         // if(count($optional_programs) == 0){
         //   $optional_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','OPTIONAL')->get();
         // }

            $grading_policies = GradingPolicy::where('nta_level_id',$student->campusProgram->program->nta_level_id)->orderBy('min_score','DESC')->where('study_academic_year_id',$sems[0]->study_academic_year_id)->get();

            foreach($student->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                    $department = $dpt;
                }
             }
          
          $points = 0;
          $credits = 0;
          foreach($results as $result){
               $points += $result->point*$result->moduleAssignment->programModuleAssignment->module->credit;
               $credits += $result->moduleAssignment->programModuleAssignment->module->credit;
          }
          
          $overall_gpa = bcdiv($points/$credits, 1,1);

         $data = [
          'semesters'=>$semesters,
          'annual_remark'=>$annual_remark,
          'results'=>$results,
          'department'=>$department,
          // 'study_academic_year'=>$study_academic_year,
          'overall_gpa'=>$overall_gpa,
          'sems'=>$sems,
          'student'=>$student,
          'grading_policies'=>$grading_policies,
          'staff'=>User::find(Auth::user()->id)->staff
         ];

         // $pdf = PDF::loadView('dashboard.academic.reports.perfomance-report', $data)->setPaper('a4','portrait');
         // return $pdf->stream();
         return view('dashboard.academic.reports.statement-of-results',$data)->withTitle('Student Statement of Results');
    }

    /**
     * Display student perfomance report
     */
    public function showStudentTranscript(Request $request, $student_id)
    {
         $student = Student::with(['campusProgram.program.departments','campusProgram.program.ntaLevel','campusProgram.campus','applicant'])->find($student_id);
        
          $results = ExaminationResult::with(['moduleAssignment.programModuleAssignment','moduleAssignment.studyAcademicYear.academicYear'])->where('student_id',$student->id)->get();
          $semesters = Semester::all();
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
              $years_of_studies[$key]['ac_year'] = StudyAcademicYear::with('academicYear')->find($yr);
              $ac_yr_id = $years_of_studies[$key]['ac_year']->id;
              $yr_of_study = $key;

               foreach ($semesters as $semester) {
                   $years_of_studies[$key][$semester->name]['results'] = ExaminationResult::whereHas('moduleAssignment',function($query) use ($ac_yr_id, $student_id){
                       $query->where('study_academic_year_id',$ac_yr_id)->where('student_id',$student_id);
                   })->whereHas('moduleAssignment.programModuleAssignment',function($query) use ($ac_yr_id, $yr_of_study,$semester){
                       $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('semester_id',$semester->id);
                   })->with(['moduleAssignment.programModuleAssignment','moduleAssignment.module','carryHistory.carrableResults'=>function($query){
                      $query->latest();
                   },'retakeHistory.retakableResults'=>function($query){
                      $query->latest();
                   },'retakeHistory.retakableResults.moduleAssignment.module','carryHistory.carryHistory.carrableResults.moduleAssignment.module'])->where('student_id',$student->id)->get();

                  $years_of_studies[$key][$semester->name]['core_programs'] = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','COMPULSORY')->where('semester_id',$semester->id)->where('campus_program_id',$student->campus_program_id)->get();
                  $years_of_studies[$key][$semester->name]['optional_programs'] = ProgramModuleAssignment::whereHas('students',function($query) use($student_id){
                   $query->where('id',$student_id);
                   })->with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('semester_id',$semester->id)->where('campus_program_id',$student->campus_program_id)->where('category','OPTIONAL')->get();
                  $years_of_studies[$key][$semester->name]['semester_remark'] = SemesterRemark::where('study_academic_year_id',$years_of_studies[$key]['ac_year']->id)->where('student_id',$student->id)->where('semester_id',$semester->id)->where('year_of_study',$yr_of_study)->first();
                  $years_of_studies[$key][$semester->name]['annual_remark'] = AnnualRemark::where('study_academic_year_id',$years_of_studies[$key]['ac_year']->id)->where('student_id',$student->id)->where('year_of_study',$yr_of_study)->first();
               }
            }
          }

          $grading_policies = GradingPolicy::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->orderBy('grade')->get();

          foreach($student->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $student->campusProgram->campus_id){
                    $department = $dpt;
                }
             }

          $points = 0;
          $credits = 0;
          foreach($results as $result){
               $points += $result->point*$result->moduleAssignment->programModuleAssignment->module->credit;
               $credits += $result->moduleAssignment->programModuleAssignment->module->credit;
          }
          
          $overall_gpa = bcdiv($points/$credits, 1,1);
          $gpa_class = GPAClassification::where('nta_level_id',$student->campusProgram->program->nta_level_id)->where('study_academic_year_id',$ac_yr_id)->where('min_gpa','<=',bcdiv($overall_gpa,1,1))->where('max_gpa','>=',bcdiv($overall_gpa,1,1))->first();
          if($gpa_class){
             $overall_remark = $gpa_class->class;
          }else{
             $overall_remark = 'N/A';
          }

         $data = [
          'semesters'=>$semesters,
          'years_of_studies'=>$years_of_studies,
          'student'=>$student,
          'department'=>$department,
          'overall_gpa'=>$overall_gpa,
          'overall_remark'=>$overall_remark,
          'grading_policies'=>$grading_policies,
          'staff'=>User::find(Auth::user()->id)->staff
         ];
         return view('dashboard.academic.reports.transcript',$data)->withTitle('Transcript');
    }


    /**
     * Display uploaded modules
     */
    public function showUploadedModules(Request $request)
    {
    	$data = [
           'campus_programs'=>$request->has('campus_id')? CampusProgram::with(['program.departments'])->where('campus_id',$request->get('campus_id'))->get() : [],
           'campus'=>Campus::find($request->get('campus_id')),
           'semesters'=>Semester::all(),
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'modules'=>$request->has('study_academic_year_id')? ProgramModuleAssignment::with(['module','examinationResults'=>function($query){
			   $query->whereNotNull('course_work_score');
		   },'moduleAssignments'=>function($query) use($request){
			   $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
		   },'moduleAssignments.staff'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->where('campus_program_id',explode('_',$request->get('campus_program_id'))[0])->where('semester_id',$request->get('semester_id'))->get() : [],
           'staff'=>User::find(Auth::user()->id)->staff,
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
    		$program = ProgramModuleAssignment::with(['examinationResults.student','module','campusProgram.program.departments','campusProgram.campus','studyAcademicYear.academicYear'])->findOrFail($id);
        foreach($program->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $program->campusProgram->campus_id){
                    $department = $dpt;
                }
             }
	    	$data = [
	            'program_module_assignment'=>$program,
	            'module'=>$program->module,
	            'program'=>$program->campusProgram->program,
	            'department'=>$department,
	            'campus'=>$program->campusProgram->campus,
	            'study_academic_year'=>$program->studyAcademicYear,
	            'result_type'=>$request->get('result_type'),
              'staff'=>User::find(Auth::user()->id)->staff
	    	];
	    	return view('dashboard.academic.reports.results-uploaded-modules-students',$data)->withTitle('Module Results');
        }catch(\Exception $e){
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display form for uploading module results
     */
    public function uploadModuleResults(Request $request)
    {
        $data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'campus'=>Campus::find($request->get('campus_id')),
           'campuses'=>Campus::all(),
           'campus_programs'=>CampusProgram::with(['program'])->get(),
           'request'=>$request
        ];
        return view('dashboard.academic.upload-module-results',$data)->withTitle('Upload Module Results');
    }
}
