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
use App\Domain\Academic\Models\AnnualRemak;
use App\Domain\Academic\Models\OverallRemark;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Student;
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

        // Determine semester, annual and overall remarks
    	if(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 1')){

        }elseif(Util::stripSpacesUpper($request->get('semester_id')) == Util::stripSpacesUpper('Semester 2')){

        }elseif($request->get('semester_id') == 'SUPPLEMENTARY'){

        }

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
               $years[$result->moduleAssignment->programModuleAssignment->year_of_study] = $result->moduleAssignment->studyAcademicYear;
    		}
    	}

    	foreach($years as $key=>$year){
    		foreach($results as $res){
    			if($res->moduleAssignment->programModuleAssignment->year_of_study == $key){
    				$years_of_studies[$key][] = $result->moduleAssignment->studyAcademicYear;
    			}
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
         $semesters = Semester::all();
         $results = ExaminationResult::with(['moduleAssignment.programModuleAssignment'=>function($query) use ($ac_yr_id,$yr_of_study){
         	 $query->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study);
         },'moduleAssignment.module'])->where('student_id',$student->id)->get();
         $core_programs = ProgramModuleAssignment::with(['module'])->where('study_academic_year_id',$ac_yr_id)->where('year_of_study',$yr_of_study)->where('category','CORE')->get();

         $data = [
         	'semesters'=>$semesters,
         	'results'=>$results,
         	'core_programs'=>$core_programs,
            'student'=>$student
         ];
         return view('dashboard.academic.reports.final-student-overall-results',$data)->withTitle('Student Results');
    }
}
