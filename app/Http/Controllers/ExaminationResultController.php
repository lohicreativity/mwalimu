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
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\AnnualRemark;
use App\Domain\Academic\Models\OverallRemark;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Student;
use App\Utils\Util;
use Auth, DB;

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
            'publications'=>$request->has('study_academic_year_id')? ResultPublication::with(['studyAcademicYear.academicYear','semester'])->where('study_academic_year_id',$request->get('study_academic_year_id'))->get() : []
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
        	


        foreach($module_assignments as $assign){
        	if($assign->course_work_process_status != 'PROCESSED'){
        		return redirect()->back()->with('error',$assign->module->name.'-'.$assign->module->code.' course works not processed');
        	}
        	if(ExaminationResult::where('final_uploaded_at',null)->where('module_assignment_id',$assign->id)->count() != 0){
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
                	$processed_result->total_score = $result->course_work_score + $result->final_score;
                }

                $grading_policy = GradingPolicy::where('nta_level_id',$assignment->module->ntaLevel->id)->where('study_academic_year_id',$assignment->studyAcademicYear->id)->where('min_score','<=',round($result->total_score))->where('max_score','>=',round($result->total_score))->first();
  
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

               if($pub = ResultPublication::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('semester_id',$request->get('semester_id'))){
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
    		DB::commit();

        return redirect()->back()->with('message','Results processed successfully');
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

        $students = Student::with(['examinationResults'=>function($query) use($assignmentIds){
        	$query->whereIn('module_assignment_id',$assignmentIds);
        }])->where('campus_program_id',$campus_program->id)->where('year_of_study',explode('_',$request->get('campus_program_id'))[2])->get();
        $data = [
           'campus'=>$campus_program->campus,
           'program'=>$campus_program->program,
           'department'=>$campus_program->program->department,
           'study_academic_year'=>$study_academic_year,
           'module_assignments'=>$module_assignments,
           'students'=>$students,
        ];
        return view('dashboard.academic.reports.final-program-results',$data)->withTitle('Final Program Results - '.$campus_program->program->name);
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
            })->get() : []
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
    	$data = [
    		'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
            'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
            'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
            'module'=>$module_assignment->module,
            'study_academic_year'=>$module_assignment->studyAcademicYear,
            'module_assignment'=>$module_assignment,
            'students'=>$students
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
    	$student = Student::where('registration_number',$request->get('registration_number'))->first();
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
           'student'=>$student
    	];
    	return view('dashboard.academic.reports.final-student-results',$data)->withTitle('Student Results');
    }

    /**
     * Display student academic year results
     */
    public function showStudentAcademicYearResults(Request $request, $student_id, $ac_yr_id, $yr_of_study)
    {
         $student = Student::find($student_id);
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
         	'study_academic_year'=>$study_academic_year,
         	'core_programs'=>$core_programs,
         	'optional_programs'=>$optional_programs,
            'student'=>$student
         ];
         return view('dashboard.academic.reports.final-student-overall-results',$data)->withTitle('Student Results');
    }

    /**
     * Update student examination result
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'student_id'=>'required',
            'module_assignment_id'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $student = Student::with(['campusProgram.program','examinationResults'])->find($request->get('student_id'));

        $module_assignments = ModuleAssignment::whereHas('programModuleAssignment',function($query) use($student){
                $query->where('campus_program_id',$student->campusProgram->id)->where('year_of_study',$student->year_of_study);
    	         })->whereHas('programModuleAssignment.campusProgram',function($query) use($campus_program){
	    	    	$query->where('program_id',$studet->campusProgram->program->id);
	    	    })->with('module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear')->where('study_academic_year_id',$request->get('study_academic_year_id'))->get();

    	if(Util::stripSpacesUpper($semester->name) == Util::stripSpacesUpper('Semester 2')){

    		    $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('category','COMPULSORY')->get();
    		}else{
    			$core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$assignment->study_academic_year_id)->where('year_of_study',$assignment->programModuleAssignment->year_of_study)->where('semester_id',$semester->id)->where('category','COMPULSORY')->get();
    		}
    		$total_credit = 0;

    	foreach($module_assignments as $assinment){

    	}


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
	            'results_type'=>$request->get('results_type')
	    	];
	    	return view('dashboard.academic.reports.results-uploaded-modules-students',$data)->withTitle('Module Results');
        }catch(\Exception $e){
        	return $e->getMessage();
        	return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
