<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ResultFile;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Models\CourseWorkComponent;
use App\Domain\Academic\Models\CourseWorkResult;
use App\Domain\Academic\Models\ExaminationResult;
use App\Domain\Academic\Models\CourseWorkResultLog;
use App\Domain\Academic\Models\ExaminationResultLog;
use App\Domain\HumanResources\Models\Staff;
use App\Domain\Registration\Models\Student;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Actions\ModuleAssignmentAction;
use App\Models\User;
use App\Utils\Util;
use App\Utils\SystemLocation;
use Validator, Auth, PDF, DB, Session;

class ModuleAssignmentController extends Controller
{
	/**
	 * Display a list of staffs to assign modules
	 */
	public function index(Request $request)
	{
    $staff = User::find(Auth::user()->id)->staff;
		$data = [
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'semester'=>$request->has('semester_id')? Semester::find($request->get('semester_id')) : null,
           'campus_programs'=>CampusProgram::with('program')->get(),

           'campus_program'=>CampusProgram::with(['program','programModuleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'));
           },'programModuleAssignments.module','programModuleAssignments.semester','programModuleAssignments.programModuleAssignmentRequests','programModuleAssignments.module.moduleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
           },'programModuleAssignments.module.moduleAssignments.staff'])->find($request->get('campus_program_id')),

           'previous_campus_program'=>CampusProgram::with(['program','programModuleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id','!=',$request->get('study_academic_year_id'))->latest();
           },'programModuleAssignments.module','programModuleAssignments.semester','programModuleAssignments.module.moduleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
           },'programModuleAssignments.module.moduleAssignments.staff'])->find($request->get('campus_program_id')),
           'staffs'=>Staff::with('designation')->where('department_id',$staff->department_id)->get(),
           'semesters'=>Semester::all(),
           'staff'=>$staff
      ];
		return view('dashboard.academic.assign-staff-modules',$data)->withTitle('Staff Module Assignment');
	}

  /**
     * Disaplay staff assigned modules 
     */
    public function showStaffAssignedModules(Request $request)
    {
        $staff = Staff::with(['assignedModules.studyAcademicYear'=>function($query){
                   $query->where('status','ACTIVE');
            }])->where('user_id',Auth::user()->id)->first();
        $data = [
           'study_academic_years'=>StudyAcademicYear::all(),
           'study_academic_year'=>$request->has('study_academic_year_id')? StudyAcademicYear::with('academicYear')->find($request->get('study_academic_year_id')) : null,
           'staff'=>$staff,
           'semesters'=>Semester::all(),
           'assignments'=>$staff? ModuleAssignment::whereHas('studyAcademicYear',function($query) use ($request){
                  $query->where('id',$request->get('study_academic_year_id'));
             })->with(['studyAcademicYear.academicYear','module','programModuleAssignment.campusProgram.program','programModuleAssignment.campusProgram.campus','programModuleAssignment.semester'])->where('staff_id',$staff->id)->latest()->paginate(20) : [],
        ];
        return view('dashboard.academic.staff-assigned-modules',$data)->withTitle('Staff Assigned Modules');
    }

    /**
     * Show assessment plans to assigned staff
     */
    public function showAssessmentPlans(Request $request,$id)
    {
        try{
            $module_assignment = ModuleAssignment::with(['module.ntaLevel','programModuleAssignment.campusProgram.program','studyAcademicYear'])->findOrFail($id);
            $policy = ExaminationPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();

            $final_upload_status = false;
             if(ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->count() != 0){
                $final_upload_status = true;
             }
            $data = [
               'module_assignment'=>$module_assignment,
               'final_upload_status'=>$final_upload_status,
               'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$id)->get(),
               'course_work_components'=>CourseWorkComponent::where('module_assignment_id',$id)->get(),
               'staff'=>User::find(Auth::user()->id)->staff,
               'policy'=>$policy
            ];
            return view('dashboard.academic.assessment-plans',$data)->withTitle('Module Assessment Plans');
        }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Display module attendance 
     */
    public function showAttendance(Request $request, $id)
    {
       try{
          $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module'])->findOrFail($id);

          $data = [
             'module_assignment'=>$module_assignment,
             'campus_program'=>CampusProgram::with(['students.registrations'=>function($query) use($module_assignment){
                   $query->where('study_academic_year_id',$module_assignment->study_academic_year_id);
             },'streams'=>function($query) use ($module_assignment){
                   $query->where('study_academic_year_id',$module_assignment->study_academic_year_id);
             },'streams.groups'])->find($module_assignment->programModuleAssignment->campus_program_id),
          ];
          return view('dashboard.academic.module-attendance',$data)->withTitle('Module Attendance');
       }catch(\Exception $e){
           return redirect()->back()->with('error','Unable to get the resource specified in this request');
       }
    }

    /**
     * Show module attendance
     */
    public function showModuleAttendance(Request $request, $id)
    {
         try{
             $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','staff','module'])->findOrFail($id);
             if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>$module_assignment->programModuleAssignment->students
                 ];
                 return view('dashboard.academic.reports.students-in-optional-module', $data);
             }else{
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>Student::whereHas('registrations',function($query) use ($module_assignment){
                         $query->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id);
                      })->get()
                 ];
                 return view('dashboard.academic.reports.students-in-core-module', $data);
             }
         }catch(\Exception $e){
             return redirect()->back()->with('error','Unable to get the resource specified in this request');
         }
    }

    /**
     * Store module assignment into database
     */
    public function store(Request $request)
    {
      $validation = Validator::make($request->all(),[
            'study_academic_year_id'=>'required',
            'staff_id'=>'required',
            'module_id'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        // if(ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('staff_id',$request->get('staff_id'))->where('module_id',$request->get('module_id'))->count() != 0){

        //      if($request->ajax()){
        //         return response()->json(array('error_messages'=>'Module already assigned for this staff in this study academic year'));
        //      }else{
        //         return redirect()->back()->with('error','Module already assigned for this staff in this study academic year');
        //      }
        // }

        if(ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('module_id',$request->get('module_id'))->count() != 0){

             if($request->ajax()){
                return response()->json(array('error_messages'=>'Module already assigned staff in this study academic year'));
             }else{
                return redirect()->back()->with('error','Module already assigned staff in this study academic year');
             }
        }

        $module = Module::find($request->get('module_id'));
        if(GradingPolicy::where('nta_level_id',$module->nta_level_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() == 0){
            return redirect()->back()->with('error','No grading policy set for this academic year');
        }


        return (new ModuleAssignmentAction)->store($request);

        //return Util::requestResponse($request,'Module assignment created successfully');
    }

    /**
     * Upload module assignment results
     */
    public function showResultsUpload(Request $request,$id)
    {
         try{
             $module_assignment = ModuleAssignment::with('assessmentPlans','module','programModuleAssignment')->findOrFail($id);
             if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $total_students_count = $module_assignment->programModuleAssignment->students()->count();
             }else{
                $total_students_count = Student::whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campusProgram->id)->count();
                
             }

             $students_with_coursework_count = CourseWorkResult::groupBy('student_id')->selectRaw('COUNT(*) as total, student_id')->where('module_assignment_id',$module_assignment->id)->get();

             $students_with_no_coursework_count = $total_students_count - count($students_with_coursework_count);
             $students_with_final_marks_count = ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('exam_type','FINAL')->whereNotNull('final_uploaded_at')->count();
             $students_with_no_final_marks_count = $total_students_count - $students_with_final_marks_count;

             $students_with_supplemetary_count = ExaminationResult::where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->where('course_work_remark','FAIL')->orWhere('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->where('final_remark','FAIL')->count();

             $students_passed_count = $students_with_supplemetary_count = ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('final_remark','!=','FAIL')->where('exam_type','FINAL')->count();
             $supp_cases_count = ExaminationResult::where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->where('final_exam_remark','FAIL')->count();
             $students_with_no_supplementary_count = $students_with_final_marks_count - $students_with_supplemetary_count;
             $students_with_abscond_count = ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->where('course_work_remark','INCOMPLETE')->orWhere('final_remark','INCOMPLETE')->count();
             $final_upload_status = false;
             if(ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->count() != 0){
                $final_upload_status = true;
             }
             $second_semester_publish_status = false;
             if(ResultPublication::whereHas('semester',function($query){
                 $query->where('name','LIKE','%2%');
             })->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('status','PUBLISHED')->count() != 0){
                $second_semester_publish_status = true;
             }
             $data = [
                'module_assignment'=>$module_assignment,
                'final_upload_status'=>$final_upload_status,
                'total_students_count'=>$total_students_count,
                'students_with_coursework_count'=>count($students_with_coursework_count),
                'students_with_no_coursework_count'=>$students_with_no_coursework_count,
                'students_with_final_marks_count'=>$students_with_final_marks_count,
                'students_with_no_final_marks_count'=>$students_with_no_final_marks_count,
                'students_with_supplemetary_count'=>$students_with_supplemetary_count,
                'students_with_no_supplementary_count'=>$students_with_no_supplementary_count,
                'students_passed_count'=>$students_passed_count,
                'students_with_abscond_count'=>$students_with_abscond_count,
                'supp_cases_count'=>$supp_cases_count,
                'second_semester_publish_status'=>$second_semester_publish_status
             ];
             return view('dashboard.academic.assessment-results',$data)->withTitle('Upload Module Assignment Results');
          }catch(\Exception $e){
              return redirect()->back()->with('error','Unable to get the resource specified in this request');
          }
    }

    /**
     * Process coursework
     */
    public function processCourseWork(Request $request)
    { 
         try{
              
              $module_assignment = ModuleAssignment::with('assessmentPlans','module','programModuleAssignment.campusProgram.program')->findOrFail($request->get('module_assignment_id'));

              $module = Module::with('ntaLevel')->find($module_assignment->module_id);
              $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
              if(!$policy){
                  return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
              }
              $module_assignment->course_work_process_status = 'PROCESSED';
              $module_assignment->save();
              // Check if all components are uploaded
              $assessment_upload_status = true;
              $assessment_plans = AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get();
              foreach ($assessment_plans as $key => $plan) {
                  if(CourseWorkResult::where('assessment_plan_id',$plan->id)->count() == 0){
                      $assessment_upload_status = false;
                  }
              }

              if(!$assessment_upload_status){
                  return redirect()->back()->with('error','Some assessment components are not uploaded');
              }

              if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get();
             }else{
                $students = Student::whereHas('registrations',function($query) use($module_assignment){
                      $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get();
             }

             DB::beginTransaction();
             foreach ($students as $key => $student) {
                $course_work = CourseWorkResult::where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->sum('score');
                $course_work_count = CourseWorkResult::whereHas('assessmentPlan',function($query) use ($module_assignment){
                     $query->where('name','LIKE','%Test%');
                  })->where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->count();

                    if($result = ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                        $exam_result = $result;
                        $exam_result->module_assignment_id = $module_assignment->id;
                        $exam_result->student_id = $student->id;
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
                        $exam_result->module_assignment_id = $module_assignment->id;
                        $exam_result->student_id = $student->id;
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
             DB::commit();
             return redirect()->back()->with('message','Course work processed successfully');
         }catch(\Exception $e){
              return $e->getMessage();
              return redirect()->back()->with('error','Unable to get the resource specified in this request');
         }
    }

    /**
     * Show total students
     */
    public function totalStudents(Request $request, $id)
    {
        try{
            $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
            if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>$module_assignment->programModuleAssignment->students()->get()
                ];

                
            }else{
                
                $data = [
                   'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'students'=>Student::whereHas('registrations',function($query) use($module_assignment){
                          $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                      })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get()
                ];
            }
            return view('dashboard.academic.reports.total-students-in-module', $data);
            
        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with course work
     */
    public function studentsWithCourseWork(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get(); 
                $registrations = Registration::whereHas('student.programModuleAssignment.moduleAssignments',function($query){
                     $query->where('id',$module_assignment->id);
                })->with(['student.courseWorkResults.assessmentPlan'])->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->get();
            }else{
                $registrations = Registration::whereHas('student',function($query) use ($module_assignment){
                        $query->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id);
                  })->with(['student.courseWorkResults.assessmentPlan'])->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->get();
            }

                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'course_work_processed'=> $module_assignment->course_work_process_status == 'PROCESSED'? true : false,
                    'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get(),
                    'registrations'=>$registrations
                ];

                return view('dashboard.academic.reports.students-with-course-work',$data);
        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

     /**
     * Show students with no course work
     */
    public function studentsWithNoCourseWork(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'course_work_processed'=> $module_assignment->course_work_process_status == 'PROCESSED'? true : false,
                    'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get(),
                    'results'=>ExaminationResult::with('student.courseWorkResults')->where('module_assignment_id',$module_assignment->id)->where('course_work_remark','INCOMPLETE')->get()
                ];

                return view('dashboard.academic.reports.students-with-no-course-work',$data);
        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Show students with final marks
     */
    public function studentsWithFinalMarks(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->get()
            ];
            return view('dashboard.academic.reports.students-with-final',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with final marks
     */
    public function studentsWithNoFinalMarks(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->whereNull('final_uploaded_at')->get()
            ];
            return view('dashboard.academic.reports.students-with-final',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with supp marks
     */
    public function studentsWithSupplementaryMarks(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->whereNotNull('final_uploaded_at')->where('course_work_remark','FAIL')->orWhere('final_remark','FAIL')->get()
            ];
            return view('dashboard.academic.reports.students-with-supplementary',$data);

        }catch(\Exception $e){
            return $e->getMessage();
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with no supp marks
     */
    public function studentsWithNoSupplementaryMarks(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->where('supp_score',null)->where('final_exam_remark','FAIL')->get()
            ];
            return view('dashboard.academic.reports.students-with-supplementary',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with supplementary
     */
    public function studentsWithSupplementary(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->where('final_exam_remark','FAIL')->get()
            ];
            return view('dashboard.academic.reports.students-with-supplementary',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }


    /**
     * Show students with abscond
     */
    public function studentsWithAbscond(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);

           $data = [
                'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                'module'=>$module_assignment->module,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::with('student')->where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->where('course_work_remark','ABSCOND')->OrWhere('final_remark','ABSCOND')->get()
            ];
            return view('dashboard.academic.reports.students-with-abscond',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Upload module assignment results
     */
    public function uploadResults(Request $request)
    {
         $validation = Validator::make($request->all(),[
            'assessment_plan_id'=>'required',
            'results_file'=>'required|mimes:csv,txt'
         ],
         [
            'assessment_plan_id'=>'Assessment is required'
         ]);

         if($validation->fails()){
             if($request->ajax()){
                return response()->json(array('error_messages'=>$validation->messages()));
             }else{
                return redirect()->back()->withInput()->withErrors($validation->messages());
             }
         }
         
         if($request->hasFile('results_file')){
          // DB::beginTransaction();
              $module_assignment = ModuleAssignment::with(['module','studyAcademicYear.academicYear','programModuleAssignment.campusProgram.program'])->find($request->get('module_assignment_id'));
              $academicYear = $module_assignment->studyAcademicYear->academicYear;

              $module = Module::with('ntaLevel')->find($module_assignment->module_id);
              $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();
              if(!$policy){
                  return redirect()->back()->withInput()->with('error','No examination policy defined for this module NTA level and study academic year');
              }

              if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                  $plan = null;
                  $assessment = 'FINAL';
                  $destination = public_path('final_results_uploads/');
              }elseif($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                  $plan = null;
                  $assessment = 'SUPP';
                  $destination = public_path('supplementary_results_uploads/');
              }else{
                  $plan = AssessmentPlan::find($request->get('assessment_plan_id'));
                  $assessment = $plan->name;
                  $destination = public_path('assessment_results_uploads/');
              }

              
              $request->file('results_file')->move($destination, $request->file('results_file')->getClientOriginalName());

              $file_name = SystemLocation::renameFile($destination, $request->file('results_file')->getClientOriginalName(),'csv', $academicYear->year.'_'.$module->code.'_'.Auth::user()->id.'_'.now()->format('YmdHms').'_'.$assessment);

              // Get students taking the module
              if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get();
                $uploaded_students = [];
                $csvFileName = $file_name;
                $csvFile = $destination.$csvFileName;
                $file_handle = fopen($csvFile, 'r');
                while (!feof($file_handle)) {
                    $line_of_text[] = fgetcsv($file_handle, 0, ',');
                }
                fclose($file_handle);
                foreach($line_of_text as $line){
                   $stud = Student::where('registration_number',trim($line[0]))->first();
                   if($stud){
                      $uploaded_students[] = $stud;
                   }
                }
                $non_opted_students = [];
                foreach($uploaded_students as $up_stud){
                   if($module_assignment->programModuleAssignment->students()->where('id',$up_stud->id)->count() == 0){
                      $non_opted_students[] = $up_stud;
                   }
                }
                if(count($non_opted_students) != 0){
                    session()->flash('non_opted_students',$non_opted_students);
                    return redirect()->back()->with('error','Uploaded students have not opted this module');
                }
              }else{
                $students = Student::whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get();
              }

              
              // Validate clean results
              $validationStatus = true;
              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                   if(floatval(trim($line[1])) < 0 || floatval(trim($line[1])) > 100){
                     $validationStatus = false;
                   }
              }

              if(!$validationStatus){
                 return redirect()->back()->with('error','Result file contains invalid data');
              }
              
              DB::beginTransaction();
              $file = new ResultFile;
              $file->file_name = $file_name;
              $file->extension = $request->file('results_file')->guessClientExtension();
              $file->mime_type = $request->file('results_file')->getClientMimeType();
              //$file->size = $request->file('results_file')->getClientSize();
              $file->module_assignment_id = $request->get('module_assignment_id');
              $file->filable_id = $plan? $plan->id : 0;
              $file->filable_type = $plan? 'assessment_plan' : null;
              $file->uploaded_by_user_id = Auth::user()->id;
              $file->save();

              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              foreach($line_of_text as $line){
                $student = Student::whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->whereHas('studentshipStatus',function($query){
                      $query->where('name','ACTIVE');
                })->where('registration_number',trim($line[0]))->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->first();

                if($student){
                  if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','FINAL')->where('status','APPROVED')->first();

                      $retake_history = RetakeHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

                      $carry_history = CarryHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

                      $result_log = new ExaminationResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->student_id = $student->id;
                      $result_log->final_score = !$special_exam? (trim($line[1])*$policy->final_min_marks)/100 : null;
                      if($carry_history){
                         $result_log->exam_category = 'CARRY';
                         $result_log->retakable_id = $carry_history->id;
                         $result_log->retakable_type = 'carry_history';
                      }
                      if($retake_history){
                         $result_log->exam_category = 'RETAKE';
                         $result_log->retakable_id = $retake_history->id;
                         $result_log->retakable_type = 'retake_history';
                      }
                      $result_log->exam_type = 'FINAL';
                      if($special_exam){
                         $result_log->final_remark = 'POSTPONED';
                      }else{
                         $result_log->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result_log->final_score? 'PASS' : 'FAIL';
                      }
                      
                      $result_log->final_uploaded_at = now();
                      $result_log->uploaded_by_user_id = Auth::user()->id;
                      $result_log->save();
                      
                      if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                          $result = $res;
                      }else{
                         $result = new ExaminationResult;
                      }
                      $result->module_assignment_id = $request->get('module_assignment_id');
                      $result->student_id = $student->id;
                      $result->final_score = !$special_exam? (trim($line[1])*$policy->final_min_mark)/100 : null;
                      $result->exam_type = 'FINAL';
                      if($carry_history){
                         $result->exam_category = 'CARRY';
                         $result->retakable_id = $carry_history->id;
                         $result->retakable_type = 'carry_history';
                      }
                      if($retake_history){
                         $result->exam_category = 'RETAKE';
                         $result->retakable_id = $retake_history->id;
                         $result->retakable_type = 'retake_history';
                      }
                      if($special_exam){
                         $result->final_remark = 'POSTPONED';
                      }else{
                         $result->final_remark = $policy->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                      }
                      $result->final_uploaded_at = now();
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }elseif($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){

                      $semester_remark = SemesterRemark::where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->first();
                      
                      $supp_upload_allowed = true;
                      if($semester_remark){
                          if($semester_remark->remark != 'FAIL&DISCO' || $Semester_remark->remark != 'PASS'){
                              $supp_upload_allowed = false;
                          }
                      }

                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','SUPP')->where('status','APPROVED')->first();
                      $grading_policy = GradingPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('grade','C')->first();
                          
                          $upload_allowed = true;
                          if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                              $result = $res;
                              if($res->final_exam_remark == 'PASS'){
                                  $upload_allowed = false; 
                              }
                          }else{
                             $result = new ExaminationResult;
                          }
                          $result->module_assignment_id = $request->get('module_assignment_id');
                          $result->student_id = $student->id;
                          if($special_exam){
                             $result->final_score = !$special_exam? (trim($line[1])*$policy->final_min_mark)/100 : null;
                             $result->final_remark = $policy->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                             $result->supp_score = null;
                          }else{
                             $result->supp_score = trim($line[1]);
                             if($result->supp_score < $policy->module_pass_mark){
                               $result->grade = 'F';
                             }else{
                                $result->grade = $grading_policy? $grading_policy->grade : 'C';
                             }
                             $result->point = $grading_policy? $grading_policy->point : 2;
                             $result->final_exam_remark = $policy->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                          }
                          $result->final_uploaded_at = now();
                          $result->uploaded_by_user_id = Auth::user()->id;
                          if($supp_upload_allowed && $upload_allowed){
                            $result->save();
                          }

                          $semester_remark = SemesterRemark::where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->first();
                      
                          $supp_upload_allowed = true;
                          if($semester_remark){
                              if($semester_remark->remark != 'FAIL&DISCO' || $Semester_remark->remark != 'PASS'){
                                  $supp_upload_allowed = false;
                              }
                          }

                          $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','SUPP')->where('status','APPROVED')->first();
                          $grading_policy = GradingPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('grade','C')->first();
                              
                              $upload_allowed = true;
                              if($res = ExaminationResultLog::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                                  $result = $res;
                                  if($res->final_exam_remark == 'PASS'){
                                      $upload_allowed = false; 
                                  }
                              }else{
                                 $result = new ExaminationResultLog;
                              }
                              $result->module_assignment_id = $request->get('module_assignment_id');
                              $result->student_id = $student->id;
                              if($special_exam){
                                 $result->final_score = !$special_exam? (trim($line[1])*$policy->final_min_mark)/100 : null;
                                 $result->final_remark = $policy->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                                 $result->supp_score = null;
                              }else{
                                 $result->supp_score = trim($line[1]);
                                 if($result->supp_score < $policy->module_pass_mark){
                                   $result->grade = 'F';
                                 }else{
                                    $result->grade = $grading_policy? $grading_policy->grade : 'C';
                                 }
                                 $result->point = $grading_policy? $grading_policy->point : 2;
                                 $result->final_exam_remark = $policy->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                              }
                              $result->final_uploaded_at = now();
                              $result->uploaded_by_user_id = Auth::user()->id;
                              if($supp_upload_allowed && $upload_allowed){
                                $result->save();
                              }
                  }elseif($request->get('assessment_plan_id') == 'CARRY'){

                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','CARRY')->where('status','APPROVED')->first();
                      $grading_policy = GradingPolicy::where('nta_level_id',$module_assignment->module->ntaLevel->id)->where('grade','C')->first();
                          
                          $upload_allowed = true;
                          if($res = ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                              $result = $res;
                              if($res->final_exam_remark == 'PASS'){
                                  $upload_allowed = false; 
                              }
                          }else{
                             $result = new ExaminationResult;
                          }
                          $result->module_assignment_id = $request->get('module_assignment_id');
                          $result->student_id = $student->id;
                          if($special_exam){
                             $result->final_score = trim($line[1]);
                             $result->final_remark = $policy->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                             $result->final_score = null;
                          }else{
                             $result->final_score = trim($line[1]);
                             if($result->final_score < $policy->module_pass_mark){
                               $result->grade = 'F';
                             }else{
                                $result->grade = $grading_policy? $grading_policy->grade : 'C';
                             }
                             $result->point = $grading_policy? $grading_policy->point : 2;
                             $result->final_exam_remark = $policy->module_pass_mark <= $result->final_score? 'PASS' : 'FAIL';
                          }
                          $result->final_uploaded_at = now();
                          $result->uploaded_by_user_id = Auth::user()->id;
                          $result->save();
                  }else{
                      $result_log = new CourseWorkResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->assessment_plan_id = $plan->id;
                      $result_log->student_id = $student->id;
                      $result_log->score = (trim($line[1])*$plan->weight)/100;
                      $result_log->uploaded_by_user_id = Auth::user()->id;
                      $result_log->save();
                      
                      if($res = CourseWorkResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->where('assessment_plan_id',$plan->id)->first()){
                          $result = $res;
                      }else{
                          $result = new CourseWorkResult;
                      }
                      $result->module_assignment_id = $request->get('module_assignment_id');
                      $result->assessment_plan_id = $plan->id;
                      $result->student_id = $student->id;
                      $result->score = (trim($line[1])*$plan->weight)/100;
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }
                }
              }
              DB::commit();
          }
          return redirect()->back()->with('message','Results uploaded successfully');
    }

    /**
     * Remove the specified assignment
     */
    public function destroy(Request $request, $id)
    {
        try{
            $assignment = ModuleAssignment::with('assessmentPlans')->findOrFail($id);
            if(count($assignment->assessmentPlans) != 0){
                return redirect()->back()->with('info','Module assignment cannot be deleted because it has assessment plans');
            }
            $assignment->delete();
            return redirect()->back()->with('message','Module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
