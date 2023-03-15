<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Models\ResultFile;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Models\Postponement;
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
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Models\SpecialExam;
use App\Domain\Academic\Models\SemesterRemark;
use App\Domain\Academic\Models\CarryHistory;
use App\Domain\Academic\Models\RetakeHistory;
use App\Domain\Academic\Models\ResultPublication; 
use App\Domain\Academic\Models\StudentProgramModuleAssignment;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Actions\ModuleAssignmentAction;
use App\Domain\Academic\Models\ModuleAssignmentRequest;
use App\Mail\StaffModuleAssigned;
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
           'campus_programs'=>CampusProgram::with(['program.departments'])->get(),

           'campus_program'=>CampusProgram::with(['program','programModuleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('year_of_study',$request->get('year_of_study'))->where('semester_id',$request->get('semester_id'));
           },'programModuleAssignments.module.departments','programModuleAssignments.semester','programModuleAssignments.programModuleAssignmentRequests','programModuleAssignments.module.moduleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
           },'programModuleAssignments.module.moduleAssignments.staff'])->find($request->get('campus_program_id')),

           'previous_campus_program'=>CampusProgram::with(['program','programModuleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id','!=',$request->get('study_academic_year_id'))->latest();
           },'programModuleAssignments.module','programModuleAssignments.semester','programModuleAssignments.module.moduleAssignments'=>function($query) use ($request){
                 $query->where('study_academic_year_id',$request->get('study_academic_year_id'));
           },'programModuleAssignments.module.moduleAssignments.staff'])->find($request->get('campus_program_id')),
           'staffs'=>Staff::with(['designation','campus','department'])->get(),
           'semesters'=>Semester::all(),
           'staff'=>$staff,
		   'module_assignment_requets'=>ModuleAssignmentRequest::with(['programModuleAssignment.moduleAssignments.staff','campusProgram.program','studyAcademicYear.academicYear','user.staff.campus'])
			   ->latest()->where('study_academic_year_id',$request->get('study_academic_year_id'))->latest()
      ];
	  //return $data;
	  //whereHas('programModuleAssignment.module.departments',function($query) use ($staff){
     //               $query->where('d',$staff->department_id);
       //        })->
		return view('dashboard.academic.assign-staff-modules',$data)->withTitle('Staff Module Assignment');
	}

  /**
   * Display a list of staffs to assign modules
   */
  public function assignmentConfirmation(Request $request)
  {
    $staff = User::find(Auth::user()->id)->staff()->with(['department'])->first();
      $data = [
           'study_academic_year'=>StudyAcademicYear::find($request->get('study_academic_year_id')),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'assignments'=>ModuleAssignment::whereHas('user.staff',function($query) use ($staff){
                    $query->where('department_id','!=',$staff->department_id)->orWhere('campus_id','!=',$staff->campus_id);
               })->whereHas('programModuleAssignment.moduleAssignments.staff',function($query) use ($staff){
                    $query->where('department_id',$staff->department_id)->where('campus_id',$staff->campus_id);
               })->with(['programModuleAssignment.moduleAssignments.staff','programModuleAssignment.campusProgram.program','studyAcademicYear.academicYear','staff.campus','user.staff'])->latest()->where('study_academic_year_id',$request->get('study_academic_year_id'))->paginate(20),
           'staffs'=>Staff::with(['campus','designation'])->where('department_id',$staff->department_id)->get(),
           'request'=>$request,
           'staff'=>$staff
      ];
      return view('dashboard.academic.assign-staff-modules-confirmation',$data)->withTitle('Module Assignments Confirmation');
  }

  /**
   * Accept confirmation
   */
  public function acceptConfirmation(Request $request,$id)
  {
      (new ModuleAssignmentAction)->acceptConfirmation($request,$id);

      return redirect()->back()->with('message','Module assignment confirmed successfully');
  }

  /**
   * Accept confirmation
   */
  public function rejectConfirmation(Request $request,$id)
  { 
      if(ModuleAssignment::find($id)->course_work_process_status == 'PROCESSED'){
          return redirect()->back()->with('error','Unable to reject. Module coursework already processed');
      }
      (new ModuleAssignmentAction)->rejectConfirmation($request,$id);

      return redirect()->back()->with('message','Module assignment rejected successfully');
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
           'request'=>$request,
           'semesters'=>Semester::all(),
           'assignments'=>$staff ? ModuleAssignment::whereHas('studyAcademicYear.moduleAssignments',function($query) use ($request){
                  $query->where('study_academic_years.id',$request->get('study_academic_year_id'))
                  ->orderBy('year_of_study', 'desc')
                  ->orderBy('semester_id', 'asc');
             })->with(['studyAcademicYear.academicYear','module','programModuleAssignment.campusProgram.program','programModuleAssignment.campusProgram.campus','programModuleAssignment.semester'])
             ->where('staff_id',$staff->id)
             ->where('confirmed',1)
             ->latest()
             ->paginate(20) : [],
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
               'policy'=>$policy,
               'module' => Module::find($module_assignment->module->id)
            ];

            if ($data['module']->course_work_based == 1) {
                return view('dashboard.academic.assessment-plans',$data)->withTitle('Module Assessment Plans');
            } else {
                return redirect('academic/staff-module-assignment/'.$data['module_assignment']->id.'/results');
            }


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
          $staff = User::find(Auth::user()->id)->staff;
          $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments'=>function($query) use($staff){
                $query->where('department_id',$staff->department_id);
           },'programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module'])->findOrFail($id);

          $data = [
             'module_assignment'=>$module_assignment,
             'module' => Module::find($module_assignment->module->id),
             'campus_program'=>CampusProgram::with(['students.studentshipStatus'=>function($query){
                   $query->where('name','ACTIVE');
             },'students.registrations'=>function($query) use($module_assignment){
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
             $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','staff','module'])->findOrFail($id);
             foreach($module_assignment->programModuleAssignment->CampusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $module_assignment->programModuleAssignment->CampusProgram->campus_id){
                    $department = $dpt;
                }
             }
             if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>$module_assignment->programModuleAssignment->students()->whereHas('registrations',function($query) use ($module_assignment){
                         $query->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','REGISTERED');
                      })->whereHas('studentshipStatus',function($query){
                        $query->where('name','ACTIVE');
                     })->get()
                 ];
                 return view('dashboard.academic.reports.students-in-optional-module', $data);
             }else{
                 $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$department,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>Student::whereHas('studentshipStatus',function($query){
                        $query->where('name','ACTIVE');
                     })->whereHas('registrations',function($query) use ($module_assignment){
                         $query->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','REGISTERED');
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

        if(ModuleAssignment::where('study_academic_year_id',$request->get('study_academic_year_id'))->where('module_id',$request->get('module_id'))->where('program_module_assignment_id',$request->get('program_module_assignment_id'))->count() != 0){

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
        
        $program_module_assignment = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));

        if(ElectivePolicy::where('campus_program_id',$program_module_assignment->campus_program_id)->where('semester_id',$program_module_assignment->semester_id)->where('year_of_study',$program_module_assignment->year_of_study)->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() == 0 && $program_module_assignment->category == 'OPTIONAL'){
            return redirect()->back()->with('error','No elective policy set for this academic year');
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
                $total_students_count = $module_assignment->programModuleAssignment->students()->whereHas('studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->count();
             }else{
                $total_students_count = Student::whereHas('studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campusProgram->id)->count();
             }

             $students_with_coursework_count = CourseWorkResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->groupBy('student_id')->selectRaw('COUNT(*) as total, student_id')->where('module_assignment_id',$module_assignment->id)->get();

             $students_with_no_coursework_count = $total_students_count - count($students_with_coursework_count);
             $students_with_final_marks_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->where('exam_type','FINAL')->whereNotNull('final_uploaded_at')->count();
             $students_with_no_final_marks_count = $total_students_count - $students_with_final_marks_count;

             $students_with_supplemetary_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->count();

             $students_passed_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->where('final_remark','!=','FAIL')->where('exam_type','FINAL')->count();
             $supp_cases_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->where('final_exam_remark','FAIL')->count();
             $students_with_no_supplementary_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->where('final_remark','!=','PASS')->where('exam_type','PASS')->count();
             $students_with_abscond_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->where('course_work_remark','INCOMPLETE')->orWhere('final_remark','INCOMPLETE')->count();
             $final_upload_status = false;
             if($module_assignment->final_upload_status == 'UPLOADED'){
                $final_upload_status = true;
             }
             $second_semester_publish_status = false;
             if(ResultPublication::whereHas('semester',function($query){
                 $query->where('name','LIKE','%2%');
             })->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('status','PUBLISHED')->count() != 0){
                $second_semester_publish_status = true;
             }

             $first_semester_publish_status = false;
             if(ResultPublication::whereHas('semester',function($query){
                 $query->where('name','LIKE','%1%');
             })->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('status','PUBLISHED')->count() != 0){
                $first_semester_publish_status = true;
             }
             
             $program_results_process_status = false;
             if(ExaminationResult::where('module_assignment_id',$module_assignment->id)->whereNotNull('final_processed_at')->count() != 0){
                $program_results_process_status = true;
             }

             $data = [
                'module_assignment'=>$module_assignment,
                'final_upload_status'=>$final_upload_status,
                'program_results_process_status'=>$program_results_process_status,
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
                'first_semester_publish_status'=>$first_semester_publish_status,
                'second_semester_publish_status'=>$second_semester_publish_status,
                'module' => Module::find($module_assignment->module->id),
                
                'staff'=>User::find(Auth::user()->id)->staff
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
			  
			  // Check if all components are uploaded
              $assessment_upload_status = true;
              $assessment_plans = AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get();

              if (sizeof($assessment_plans) == 0) {
                $assessment_upload_status = false;
              } else {

                foreach ($assessment_plans as $key => $plan) {
                    if(CourseWorkResult::where('assessment_plan_id',$plan->id)->count() == 0){
                        $assessment_upload_status = false;
                    }
                }
              }

              

              if(!$assessment_upload_status){
                  return redirect()->back()->with('error','Some assessment components are not uploaded');
              }

              $module = Module::with('ntaLevel')->find($module_assignment->module_id);
              $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();

              $module_assignment->course_work_process_status = 'PROCESSED';
              $module_assignment->save();
              

              if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get();
             }else{
                $students = Student::whereHas('studentshipStatus',function($query){
                      $query->where('name','ACTIVE');
                })->whereHas('registrations',function($query) use($module_assignment){
                      $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get();
             }

             DB::beginTransaction();
             foreach ($students as $key => $student) {
                $course_work = CourseWorkResult::where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->sum('score');
                $student_course_work_count = CourseWorkResult::where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->count();

                $course_work_count = CourseWorkResult::whereHas('assessmentPlan',function($query) use ($module_assignment){
                     $query->where('name','LIKE','%Test%');
                  })->where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->count();

                      $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();
                  
                    if($result = ExaminationResult::where('module_assignment_id',$module_assignment->id)->where('student_id',$student->id)->where('exam_type','FINAL')->first()){
                        $exam_result = $result;
                        $exam_result->module_assignment_id = $module_assignment->id;
                        $exam_result->student_id = $student->id;
                        $exam_result->course_work_score = $course_work_count < 2? null : $course_work;
                        if(is_null($course_work) || $course_work_count < 2){
                           $exam_result->course_work_remark = 'INCOMPLETE';
                        }else{
                           $exam_result->course_work_remark = $module_assignment->programModuleAssignment->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
                        }
                        if($postponement){
                           $exam_result->course_work_score = null;
                           $exam_result->course_work_remark = 'POSTPONED';
                        }
                        $exam_result->processed_by_user_id = Auth::user()->id;
                        $exam_result->processed_at = now();
                        $exam_result->save();
                    }else{
                        // if($student_course_work_count != 0){
                        $exam_result = new ExaminationResult;
                        $exam_result->module_assignment_id = $module_assignment->id;
                        $exam_result->student_id = $student->id;
                        $exam_result->course_work_score = $course_work_count < 2? null : $course_work;

                        if(is_null($course_work) || $course_work_count < 2){
                           $exam_result->course_work_remark = 'INCOMPLETE';
                        }else{
                           $exam_result->course_work_remark = $module_assignment->programModuleAssignment->course_work_pass_score <= $course_work? 'PASS' : 'FAIL';
                        }
                        if($postponement){
                           $exam_result->course_work_score = null;
                           $exam_result->course_work_remark = 'POSTPONED';
                        }
                        $exam_result->uploaded_by_user_id = Auth::user()->id;
                        $exam_result->processed_by_user_id = Auth::user()->id;
                        $exam_result->processed_at = now();
                        $exam_result->save();
                        // }
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
     * Show total students CSV
     */
    public function totalStudentsFormattedCSV(Request $request, $id)
    {
        try{
            $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.department','programModuleAssignment.campusProgram.campus',
														 'studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
            if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
/* 				return DB::table('module_assignments')
                    ->join('program_module_assignments', 'module_assignments.program_module_assignment_id', '=', 'program_module_assignments.id')
                    ->join('student_program_module_assignment', 'program_module_assignments.id', '=', 'student_program_module_assignment.program_module_assignment_id')
                    ->join('students', 'student_program_module_assignment.student_id', '=', 'students.id')
                    ->join('studentship_statuses', 'students.studentship_status_id', '=', 'studentship_statuses.id')
                    ->where('studentship_statuses.name', 'ACTIVE')
                    ->select('students.registration_number')
                    ->orderBy('students.registration_number')
                    ->get();
					 */
					 
/* 					$students_with_supplemetary_count = ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->count();
				 */
				return ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->whereHas('student.registrations',
                        function($query){
                    $query->where('status','REGISTERED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->get(),

                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students' =>$module_assignment->programModuleAssignment->students()->whereHas('studentshipStatus',function($query){
                        $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                     })->get()
                ];

                
            }else{
			
                $data = [
                   'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$module_assignment->programModuleAssignment->campusProgram->program->department,
                    'module'=>$module_assignment->module,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'students'=>Student::whereHas('studentshipStatus',function($query){
                          $query->where('name','ACTIVE');
                    })->whereHas('registrations',function($query) use($module_assignment){
                          $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                      })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->orderBy('registration_number')->get()
                ];
            }
              $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'application/csv',
                      'Content-Disposition' => 'attachment; filename="'.$module_assignment->module->code.'_'.$module_assignment->studyAcademicYear->academicYear->year.'.csv";',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

              $list = $data['students'];

              # add headers for each column in the CSV download
              // array_unshift($list, array_keys($list[0]));

             $callback = function() use ($list) 
              {
                  $file_handle = fopen('php://output', 'w');
                  foreach ($list as $row) {
					fputcsv($file_handle, [$row->registration_number]);
                  }
                  fclose($file_handle);
              };

              return response()->stream($callback, 200, $headers);
            
        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show total students
     */
    public function totalStudents(Request $request, $id)
    {
        try{
            $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
            foreach($module_assignment->programModuleAssignment->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $module_assignment->programModuleAssignment->campusProgram->campus_id){
                    $department = $dpt;
                }
             }
            if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$department,
                    'module'=>$module_assignment->module,
					'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'staff'=>$module_assignment->staff,
                    'module'=>$module_assignment->module,
                    'students'=>$module_assignment->programModuleAssignment->students()->whereHas('studentshipStatus',function($query){
                        $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                     })->whereHas('registrations',function($query) use($module_assignment) {
                          $query->where('status','REGISTERED');})->get(),
					'semester'=>$module_assignment->programModuleAssignment->semester_id
                ];
                
            }else{
                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$department,
                    'module'=>$module_assignment->module,
					'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'students'=>Student::whereHas('studentshipStatus',function($query){
                          $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                      })->whereHas('registrations',function($query) use($module_assignment) {
                          $query->where('status','REGISTERED'); 
                          $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)
						  ->where('semester_id',$module_assignment->programModuleAssignment->semester_id)
						  ->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                      })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->get(),
					'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
           foreach($module_assignment->programModuleAssignment->campusProgram->program->departments as $dpt){
                if($dpt->pivot->campus_id == $module_assignment->programModuleAssignment->campusProgram->campus_id){
                    $department = $dpt;
                }
            }
           if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get(); 
                $registrations = Registration::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name', 'RESUMED');
                })->where('status','REGISTERED')->whereHas('student.options.moduleAssignments',function($query) use($module_assignment){
                     $query->where('id',$module_assignment->id);
                })->with(['student.courseWorkResults.assessmentPlan','student.courseWorkResults'=>function($query) use($module_assignment){
					    $query->where('module_assignment_id',$module_assignment->id);
				  }])->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->get();
            }else{
                $registrations = Registration::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name', 'RESUMED');
                })->where('status','REGISTERED')->whereHas('student',function($query) use ($module_assignment){
                        $query->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id);
                  })->with(['student.courseWorkResults.assessmentPlan','student.courseWorkResults'=>function($query) use($module_assignment){
					    $query->where('module_assignment_id',$module_assignment->id);
				  }])->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)
				  ->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id)
				  ->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->get();
            }

                $data = [
                    'program'=>$module_assignment->programModuleAssignment->campusProgram->program,
                    'campus'=>$module_assignment->programModuleAssignment->campusProgram->campus,
                    'department'=>$department,
                    'module'=>$module_assignment->module,
                    'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
                    'study_academic_year'=>$module_assignment->studyAcademicYear,
                    'course_work_processed'=> $module_assignment->course_work_process_status == 'PROCESSED'? true : false,
                    'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get(),
                    'registrations'=>$registrations,
					'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                    'course_work_processed'=> $module_assignment->course_work_process_status == 'PROCESSED'? true : false,
                    'assessment_plans'=>AssessmentPlan::where('module_assignment_id',$module_assignment->id)->get(),
                    'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->with('student.courseWorkResults')->where('module_assignment_id',$module_assignment->id)->where('course_work_remark','INCOMPLETE')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->orWhere('name','RESUMED');
                })->whereHas('student.registrations',
                        function($query){
                    $query->where('status','REGISTERED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->whereNull('final_uploaded_at')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->whereHas('student.registrations',
                        function($query){
                    $query->where('status','REGISTERED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
            ];
            return view('dashboard.academic.reports.students-with-supplementary',$data);

        }catch(\Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Show students with no supp marks
     */
    public function studentsWithNoSupplementaryMarks(Request $request,$id)
    {
        try{
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->where('supp_score',null)->where('final_exam_remark','FAIL')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
           $module_assignment = ModuleAssignment::with(['programModuleAssignment.campusProgram.program.departments','programModuleAssignment.campusProgram.campus','studyAcademicYear.academicYear','programModuleAssignment.module','programModuleAssignment.students','module'])->findOrFail($id);
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
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE')->OrWhere('name','RESUMED');
                })->whereHas('student.registrations',
                        function($query){
                    $query->where('status','REGISTERED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->where('final_exam_remark','FAIL')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
				'year_of_study'=>$module_assignment->programModuleAssignment->year_of_study,
                'study_academic_year'=>$module_assignment->studyAcademicYear,
                'results'=>ExaminationResult::whereHas('student.studentshipStatus',function($query){
                    $query->where('name','ACTIVE');
                })->whereHas('student.registrations',
                        function($query){
                    $query->where('status','REGISTERED');
                })->with('student')->where('module_assignment_id',$module_assignment->id)->where('final_uploaded_at','!=',null)->where('course_work_remark','ABSCOND')->OrWhere('final_remark','ABSCOND')->get(),
				'semester'=>$module_assignment->programModuleAssignment->semester_id
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
              if($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                    $all_students = Student::whereHas('studentshipStatus',function($query){
                            $query->where('name','ACTIVE')->orWhere('name','POSTPONED');
                      })->whereHas('academicStatus',function($query){
                            $query->where('name','PASS')->orWhere('name','FRESHER')->orWhere('name','RETAKE')->orWhere('name','POSTPONED')->orWhere('name','SUPP');
                      })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->get();
              }else{
                    $all_students = Student::whereHas('studentshipStatus',function($query){
                            $query->where('name','ACTIVE')->orWhere('name','POSTPONED');
                      })->whereHas('academicStatus',function($query){
                            $query->where('name','PASS')->orWhere('name','FRESHER')->orWhere('name','RETAKE')->orWhere('name','POSTPONED');
                      })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->get();
              }
              

              foreach($all_students as $std){
                  if(!$reg = Registration::where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id)->where('student_id',$std->id)->first()){
                        $registration = new Registration;
                        $registration->student_id = $std->id;
                        $registration->year_of_study = $module_assignment->programModuleAssignment->year_of_study;
                        $registration->study_academic_year_id = $module_assignment->programModuleAssignment->study_academic_year_id;
                        $registration->semester_id = $module_assignment->programModuleAssignment->semester_id;
                        $registration->save();
                  }
              }

              $academicYear = $module_assignment->studyAcademicYear->academicYear;

              $module = Module::with('ntaLevel')->find($module_assignment->module_id);
              $policy = ExaminationPolicy::where('nta_level_id',$module->ntaLevel->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('type',$module_assignment->programModuleAssignment->campusProgram->program->category)->first();


              if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                  $plan = null;
                  $assessment = 'FINAL';
                  $destination = public_path('final_results_uploads/');
                  ModuleAssignment::where('id',$module_assignment->id)->update(['final_upload_status'=>'UPLOADED']);
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

              $uploaded_students = [];
              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text_1[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              $invalid_students_entries = [];
              $missing_students = [];
              foreach($line_of_text_1 as $line){
                 if(gettype($line) != 'boolean'){
                    $stud = Student::where('registration_number',trim($line[0]))->first();
                     if($stud && (!empty($line[1]) || $line[1] == 0)){
                        $uploaded_students[] = $stud;
                     }elseif($stud && empty($line[1])){
                        $missing_students[] = $stud;
                     }else{
                        $invalid_students_entries[] = $line[0];
                     }
                 }
                 
              }

              // Get students taking the module
              if($module_assignment->programModuleAssignment->category == 'OPTIONAL'){
                $students = $module_assignment->programModuleAssignment->students()->get();
                
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
                if($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                    $students = $module_assignment->programModuleAssignment->students()->get();
                
                    $non_opted_students = [];
                    foreach($uploaded_students as $up_stud){
                       if($module_assignment->programModuleAssignment->students()->whereHas('academicStatus',function($query){
                          $query->where('name','POSTPONED')->orWhere('name','SUPP');
                       })->where('id',$up_stud->id)->count() == 0){
                          $non_opted_students[] = $up_stud;
                       }
                    }
                    if(count($non_opted_students) != 0){
                        session()->flash('non_opted_students',$non_opted_students);
                        return redirect()->back()->with('error','Uploaded students have not opted this module');
                    }
                }
              }else{
                $students = Student::whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->with('academicStatus')->get();

                $invalid_students = [];
                foreach($uploaded_students as $up_stud){
                   if(Student::whereHas('registrations',function($query) use($module_assignment){
                     $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('registration_number',$up_stud->registration_number)->count() == 0){
                      $invalid_students[] = $up_stud;
                   }
                }
                if(count($invalid_students) != 0){
                     session()->flash('invalid_students',$invalid_students);
                     return redirect()->back()->with('error','Uploaded students do not exists');
                }

                if($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                    $invalid_students = [];
                    foreach($uploaded_students as $up_stud){
                       if(Student::whereHas('academicStatus',function($query){
                          $query->where('name','SUPP')->orWhere('name','POSTPONED');
                       })->whereHas('registrations',function($query) use($module_assignment){
                         $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                    })->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->where('registration_number',$up_stud->registration_number)->count() == 0){
                          $invalid_students[] = $up_stud;
                       }
                    }
                    if(count($invalid_students) != 0){
                         session()->flash('invalid_students',$invalid_students);
                         return redirect()->back()->with('error','Uploaded students do not exists');
                    }
                }
              }

              
              foreach($students as $stud){
                  $student_present = false;
                  foreach($uploaded_students as $up_stud){
                      if($up_stud->id == $stud->id){
                          $student_present = true;
                      }
                  }
                  if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                    if(ExaminationResult::where('student_id',$stud->id)->whereHas('moduleAssignment.programModuleAssignment',function($query) use($stud, $module_assignment){
                         $query->where('year_of_study',$stud->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id);
                    })->where('module_assignment_id',$module_assignment->id)->whereNotNull('final_uploaded_at')->count() != 0){
                        $student_present = true;
                    }
                  }elseif($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                    if(ExaminationResult::where('student_id',$stud->id)->whereHas('moduleAssignment.programModuleAssignment',function($query) use($stud, $module_assignment){
                         $query->where('year_of_study',$stud->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id);
                    })->where('module_assignment_id',$module_assignment->id)->whereNotNull('supp_score')->count() != 0){
                        $student_present = true;
                    }
                  }else{
                    if(ExaminationResult::where('student_id',$stud->id)->whereHas('moduleAssignment.programModuleAssignment',function($query) use($stud, $module_assignment){
                         $query->where('year_of_study',$stud->year_of_study)->where('semester_id',$module_assignment->programModuleAssignment->semester_id);
                    })->where('module_assignment_id',$module_assignment->id)->whereNull('final_uploaded_at')->count() != 0){
                        $student_present = true;
                    }
                  }
                  if(!$student_present){
                      $missing_students[] = $stud;
                  }
              }

              foreach($missing_students as $student){
                if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                  if(ExaminationResult::where('module_assignment_id',$request->get('module_assignment_id'))->where('student_id',$student->id)->whereNotNull('final_score')->count() == 0){
                  $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','FINAL')->where('status','APPROVED')->first();
                  $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();
                      
                      if($student->academicStatus->status != 'RETAKE'){
                      $result_log = new ExaminationResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->student_id = $student->id;
                      $result_log->final_score = null;
                      
                      $result_log->exam_type = 'FINAL';
                      if($special_exam || $postponement){
                         $result_log->final_remark = 'POSTPONED';
                      }else{
                         $result_log->final_remark = 'INCOMPLETE';
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
                      $result->final_score = null;
                      $result->exam_type = 'FINAL';
                      if($special_exam || $postponement){
                         $result->final_remark = 'POSTPONED';
                      }else{
                         $result->final_remark = 'INCOMPLETE';
                      }
                      $result->final_uploaded_at = now();
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                      }
                    }
                  }
              }

              
              // Validate clean results
              $validationStatus = true;
              $csvFileName = $file_name;
              $csvFile = $destination.$csvFileName;
              $file_handle = fopen($csvFile, 'r');
              while (!feof($file_handle)) {
                  $line_of_text_2[] = fgetcsv($file_handle, 0, ',');
              }
              fclose($file_handle);
              $invalidEntries = [];
              foreach($line_of_text_2 as $line){
                   if(gettype($line) != 'boolean'){
                       if((floatval(trim($line[1])) < 0 || floatval(trim($line[1])) > 100 || (!is_numeric(trim($line[1]))) && !empty($line[1]))){
                         $validationStatus = false;
                         $invalidEntries[] = trim($line[0]);
                       }
                   }
              }

              if(!$validationStatus){
                 return redirect()->back()->with('error','Invalid data. Please check registration number '.implode(', ', $invalidEntries));
              }

              if(count($invalid_students_entries) != 0){
                 return redirect()->back()->with('error','Invalid registration number. Please check registration number '.implode(', ', $invalid_students_entries));
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
				  //whereHas('registrations',function($query) use($module_assignment){
                   //  $query->where('year_of_study',$module_assignment->programModuleAssignment->year_of_study)->where('semester_id',$module_assignment->programMo//duleAssignment->semester_id)->where('study_academic_year_id',$module_assignment->programModuleAssignment->study_academic_year_id);
                //})->whereHas('studentshipStatus',function($query){
                     // $query->where('name','ACTIVE');
               // })->
                if(gettype($line) != 'boolean'){
                $student = Student::where('registration_number',trim($line[0]))->where('campus_program_id',$module_assignment->programModuleAssignment->campus_program_id)->first();
                
                if($student && (!empty($line[1]) || $line[1] == 0)){

                  if($request->get('assessment_plan_id') == 'FINAL_EXAM'){
                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','FINAL')->where('status','APPROVED')->first();
                      $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();


                      $retake_history = RetakeHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

                      $carry_history = CarryHistory::whereHas('moduleAssignment',function($query) use($module){
                            $query->where('module_id',$module->id);
                      })->where('student_id',$student->id)->first();

                      $result_log = new ExaminationResultLog;
                      $result_log->module_assignment_id = $request->get('module_assignment_id');
                      $result_log->student_id = $student->id;
                      if($special_exam || $postponement){
                        $result_log->final_score = null;
                      }else{
                        $result_log->final_score = (trim($line[1])*$module_assignment->programModuleAssignment->final_min_mark)/100;
                      }
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
                      if($special_exam || $postponement){
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
                      if($special_exam || $postponement){
                        $result->final_score = null;
                      }else{
                        $result->final_score = (trim($line[1])*$module_assignment->programModuleAssignment->final_min_mark)/100;
                      }
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
                      if($special_exam || $postponement){
                         $result->final_remark = 'POSTPONED';
                      }else{
                         $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                      }
                      $result->final_uploaded_at = now();
                      $result->uploaded_by_user_id = Auth::user()->id;
                      $result->save();
                  }elseif($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){

                      $semester_remark = SemesterRemark::where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->first();
                      
                      $supp_upload_allowed = true;
                      if($semester_remark){
                          if($semester_remark->remark != 'FAIL&DISCO' || $semester_remark->remark != 'PASS'){
                              $supp_upload_allowed = false;
                          }
                      }

                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','SUPP')->where('status','APPROVED')->first();
                      $final_special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','FINAL')->where('status','APPROVED')->first();
                      $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();
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
                          if($special_exam || $postponement){
                             $result->final_score = !$special_exam || !$postponement? (trim($line[1])*$module_assignment->programModuleAssignment->final_min_mark)/100 : null;
                             $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                             $result->supp_score = null;
                          }else{
                             $result->supp_score = trim($line[1]);
                             // if($result->supp_score < $module_assignment->programModuleAssignment->module_pass_mark){
                             //   $result->grade = 'F';
                             // }else{
                             //    $result->grade = $grading_policy? $grading_policy->grade : 'C';
                             // }
                             // $result->point = $grading_policy? $grading_policy->point : 2;
                             // $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                          }
                          if($final_special_exam){
                                 $result->final_score = (trim($line[1])*$module_assignment->programModuleAssignment->final_min_mark)/100;
                                 $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                                 $result->supp_score = null;
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
                              if($special_exam || $postponement){
                                 $result->final_score = !$special_exam && !$postponement? (trim($line[1])*$module_assignment->programModuleAssignment->final_min_mark)/100 : null;
                                 $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                                 $result->supp_score = null;
                              }else{
                                 $result->supp_score = trim($line[1]);
                                 if($result->supp_score < $module_assignment->programModuleAssignment->module_pass_mark){
                                   $result->grade = 'F';
                                 }else{
                                    $result->grade = $grading_policy? $grading_policy->grade : 'C';
                                 }
                                 $result->point = $grading_policy? $grading_policy->point : 2;
                                 $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->supp_score? 'PASS' : 'FAIL';
                              }
                              $result->final_uploaded_at = now();
                              $result->uploaded_by_user_id = Auth::user()->id;
                              if($supp_upload_allowed && $upload_allowed){
                                $result->save();
                              }
                  }elseif($request->get('assessment_plan_id') == 'CARRY'){

                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','CARRY')->where('status','APPROVED')->first();
                      $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();
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
                          if($special_exam || $postponement){
                             $result->final_score = trim($line[1]);
                             $result->final_remark = $module_assignment->programModuleAssignment->final_pass_score <= $result->final_score? 'PASS' : 'FAIL';
                             $result->final_score = null;
                          }else{
                             $result->final_score = trim($line[1]);
                             if($result->final_score < $module_assignment->programModuleAssignment->module_pass_mark){
                               $result->grade = 'F';
                             }else{
                                $result->grade = $grading_policy? $grading_policy->grade : 'C';
                             }
                             $result->point = $grading_policy? $grading_policy->point : 2;
                             $result->final_exam_remark = $module_assignment->programModuleAssignment->module_pass_mark <= $result->final_score? 'PASS' : 'FAIL';
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
                }elseif($student && !empty($line[1]) && isset($line[2])){
                    return redirect()->back()->with('error','Invalid entries in column B of the uploaded file');
                }elseif($student && empty($line[1])){
                    if($request->get('assessment_plan_id') == 'SUPPLEMENTARY'){
                        $semester_remark = SemesterRemark::where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('study_academic_year_id',$request->get('study_academic_year_id'))->where('student_id',$student->id)->first();
                      
                      $supp_upload_allowed = true;
                      if($semester_remark){
                          if($semester_remark->remark != 'FAIL&DISCO' || $semester_remark->remark != 'PASS'){
                              $supp_upload_allowed = false;
                          }
                      }

                      $special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','SUPP')->where('status','APPROVED')->first();
                      $final_special_exam = SpecialExam::where('student_id',$student->id)->where('module_assignment_id',$module_assignment->id)->where('type','FINAL')->where('status','APPROVED')->first();
                      $postponement = Postponement::where('student_id',$student->id)->where('study_academic_year_id',$module_assignment->study_academic_year_id)->where('semester_id',$module_assignment->programModuleAssignment->semester_id)->where('status','POSTPONED')->first();
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
                          if($special_exam || $postponement){
                             $result->final_score = null;
                             $result->final_remark = 'INCOMPLETE';
                             $result->supp_score = null;
                             $result->supp_remark = 'INCOMPLETE';
                          }else{
                             $result->final_remark = 'INCOMPLETE';
                             $result->supp_score = null;
                             $result->supp_remark = 'INCOMPLETE';
                          }
                          $result->final_uploaded_at = now();
                          $result->uploaded_by_user_id = Auth::user()->id;
                          if($supp_upload_allowed && $upload_allowed){
                            $result->save();
                          }

                    }
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
            if(AssessmentPlan::has('courseWorkResults')->where('module_assignment_id',$id)->count() != 0){
                return redirect()->back()->with('info','Module assignment cannot be deleted because it has assessment plans');
            }
            $assignment->delete();
            return redirect()->back()->with('message','Module assignment deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
