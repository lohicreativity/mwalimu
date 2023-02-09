<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\StudyAcademicYearController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\CampusProgramController;
use App\Http\Controllers\ModuleAssignmentController;
use App\Http\Controllers\ModuleAssignmentRequestController;
use App\Http\Controllers\AssessmentPlanController;
use App\Http\Controllers\CourseWorkComponentController;
use App\Http\Controllers\ProgramModuleAssignmentController;
use App\Http\Controllers\ProgramModuleAssignmentRequestController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\StreamComponentController;
use App\Http\Controllers\ElectivePolicyController;
use App\Http\Controllers\ElectiveModuleLimitController;
use App\Http\Controllers\GradingPolicyController;
use App\Http\Controllers\ExaminationPolicyController;
use App\Http\Controllers\ExaminationIrregularityController;
use App\Http\Controllers\PostponementController;
use App\Http\Controllers\SpecialExamController;
use App\Http\Controllers\ExaminationResultController;
use App\Http\Controllers\ResultPublicationController;
use App\Http\Controllers\CourseWorkResultController;
use App\Http\Controllers\ApplicationWindowController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\GraduantController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GPAClassificationController;
use App\Http\Controllers\TranscriptRequestController;
use App\Http\Controllers\PerformanceReportRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth:sanctum', 'verified'])->group(function(){

	Route::get('semesters', [SemesterController::class,'index'])->name('semesters');
	Route::post('semester/store', [SemesterController::class,'store']);
	Route::post('semester/update', [SemesterController::class,'update']);
	Route::get('semester/{id}/destroy', [SemesterController::class,'destroy']);
	Route::get('semester/{id}/activate', [SemesterController::class,'activate']);
	Route::get('semester/{id}/deactivate', [SemesterController::class,'deactivate']);


	Route::get('departments', [DepartmentController::class,'index']);
	Route::post('department/store', [DepartmentController::class,'store']);
	Route::post('department/update', [DepartmentController::class,'update']);
	Route::get('department/{id}/destroy', [DepartmentController::class,'destroy']);

	Route::get('programs', [ProgramController::class,'index']);
	Route::post('program/store', [ProgramController::class,'store']);
	Route::post('program/update', [ProgramController::class,'update']);
	Route::get('program/{id}/destroy', [ProgramController::class,'destroy']);


	Route::get('modules', [ModuleController::class,'index']);
	Route::post('module/store', [ModuleController::class,'store']);
	Route::post('module/update', [ModuleController::class,'update']);
	Route::get('module/{id}/destroy', [ModuleController::class,'destroy']);
	Route::get('module/{id}/download-syllabus', [ModuleController::class,'downloadSyllabus']);


	Route::get('module-assignments', [ModuleAssignmentController::class,'index']);
	Route::get('module-assignment/confirmation', [ModuleAssignmentController::class,'assignmentConfirmation']);
	Route::get('module-assignment/{id}/confirmation/accept', [ModuleAssignmentController::class,'acceptConfirmation']);
	Route::get('module-assignment/{id}/confirmation/reject', [ModuleAssignmentController::class,'rejectConfirmation']);
	Route::post('module-assignment/store', [ModuleAssignmentController::class,'store']);
	Route::get('module-assignment/{id}/destroy', [ModuleAssignmentController::class,'destroy']);
	Route::get('module-assignment/{id}/examination-irregularities',[ExaminationIrregularityController::class, 'index']);
	Route::get('module-assignment/{id}/special-exams',[SpecialExamController::class, 'index']);


	Route::get('module-assignment-requests', [ModuleAssignmentRequestController::class,'index']);
	Route::post('module-assignment-request/store', [ModuleAssignmentRequestController::class,'store']);
	Route::get('module-assignment-request/{id}/destroy', [ModuleAssignmentRequestController::class,'destroy']);


	Route::get('staff-module-assignments', [ModuleAssignmentController::class,'showStaffAssignedModules']);
	Route::get('staff-module-assignment/{id}/assessment-plans', [ModuleAssignmentController::class,'showAssessmentPlans']);
	Route::get('staff-module-assignment/{id}/syllabus', [ModuleAssignmentController::class,'showSyllabus']);
	Route::get('staff-module-assignment/{id}/attendance', [ModuleAssignmentController::class,'showAttendance']);
	Route::get('staff-module-assignment/{id}/module-attendance', [ModuleAssignmentController::class,'showModuleAttendance']);
	Route::get('staff-module-assignment/{id}/results', [ModuleAssignmentController::class,'showResultsUpload']);
	Route::get('staff-module-assignment/{id}/results/compute-course-work', [ModuleAssignmentController::class,'computeCourseWork']);
	Route::get('staff-module-assignment/results/compute-results', [ModuleAssignmentController::class,'computeResults']);
	Route::get('staff-module-assignment/{id}/results/download-course-work', [ModuleAssignmentController::class,'downloadCourseWork']);
	Route::post('module-assignment-result/store',[ModuleAssignmentController::class,'uploadResults']);
	Route::post('staff-module-assignment/process-course-work',[ModuleAssignmentController::class,'processCourseWork']);
	Route::post('module-assignment/process-results',[ModuleAssignmentController::class,'processFinalResults']);
	Route::get('staff-module-assignment/{id}/csv/download', [ModuleAssignmentController::class,'totalStudentsFormattedCSV']);
	Route::get('staff-module-assignment/{id}/results/total-students', [ModuleAssignmentController::class,'totalStudents']);
	Route::get('staff-module-assignment/{id}/results/students-with-course-work', [ModuleAssignmentController::class,'studentsWithCourseWork']);
	Route::get('staff-module-assignment/{id}/results/students-with-no-course-work', [ModuleAssignmentController::class,'studentsWithNoCourseWork']);
	Route::get('staff-module-assignment/{id}/results/students-with-final-marks', [ModuleAssignmentController::class,'studentsWithFinalMarks']);
	Route::get('staff-module-assignment/{id}/results/students-with-no-final-marks', [ModuleAssignmentController::class,'studentsWithNoFinalMarks']);
	Route::get('staff-module-assignment/{id}/results/download-course-work', [ModuleAssignmentController::class,'studentsWithNoFinalMarks']);
	Route::get('staff-module-assignment/{id}/results/students-with-supplementary', [ModuleAssignmentController::class,'studentsWithSupplementary']);
	Route::get('staff-module-assignment/{id}/results/students-with-supplementary-marks', [ModuleAssignmentController::class,'studentsWithSupplementaryMarks']);
	Route::get('staff-module-assignment/{id}/results/students-with-no-supplementary-marks', [ModuleAssignmentController::class,'studentsWithNoSupplementaryMarks']);
	Route::get('staff-module-assignment/{id}/results/students-with-abscond', [ModuleAssignmentController::class,'studentsWithAbscond']);


	Route::post('assessment-plan/store',[AssessmentPlanController::class,'store']);
	Route::post('assessment-plan/update',[AssessmentPlanController::class,'update']);
	Route::get('assessment-plan/{mod_assign_id}/reset',[AssessmentPlanController::class,'reset']);

	Route::post('course-work-component/store',[CourseWorkComponentController::class,'store']);


	Route::get('campuses', [CampusController::class,'index']);
	Route::post('campus/store', [CampusController::class,'store']);
	Route::post('campus/update', [CampusController::class,'update']);
	Route::get('campus/{id}/destroy', [CampusController::class,'destroy']);


	Route::get('elective-policies', [ElectivePolicyController::class,'index']);
	Route::post('elective-policy/store', [ElectivePolicyController::class,'store']);
	Route::post('elective-policy/update', [ElectivePolicyController::class,'update']);
	Route::get('elective-policy/{id}/destroy', [ElectivePolicyController::class,'destroy']);


	Route::get('elective-module-limits', [ElectiveModuleLimitController::class,'index']);
	Route::post('elective-module-limit/store', [ElectiveModuleLimitController::class,'store']);
	Route::post('elective-module-limit/update', [ElectiveModuleLimitController::class,'update']);
	Route::get('elective-module-limit/{id}/destroy', [ElectiveModuleLimitController::class,'destroy']);


	Route::get('campus/{id}/campus-programs', [CampusProgramController::class,'index']);
	Route::post('campus/campus-program/store', [CampusProgramController::class,'store']);
	Route::post('campus/campus-program/update', [CampusProgramController::class,'update']);
	Route::get('campus/campus-program/{id}/destroy', [CampusProgramController::class,'destroy']);
	Route::get('campus/campus-program/{id}/attendance', [CampusProgramController::class,'showAttendance']);


	Route::get('academic-years', [AcademicYearController::class,'index']);
	Route::post('academic-year/store', [AcademicYearController::class,'store']);
	Route::post('academic-year/update', [AcademicYearController::class,'update']);
	Route::get('academic-year/{id}/destroy', [AcademicYearController::class,'destroy']);

	Route::get('study-academic-years', [StudyAcademicYearController::class,'index']);
	Route::post('study-academic-year/store', [StudyAcademicYearController::class,'store']);
	Route::post('study-academic-year/update', [StudyAcademicYearController::class,'update']);
	Route::get('study-academic-year/{id}/activate', [StudyAcademicYearController::class,'activate']);
	Route::get('study-academic-year/{id}/deactivate', [StudyAcademicYearController::class,'deactivate']);
	Route::get('study-academic-year/{id}/enable-nhif', [StudyAcademicYearController::class,'enableNhif']);
	Route::get('study-academic-year/{id}/disable-nhif', [StudyAcademicYearController::class,'disableNhif']);
	Route::get('study-academic-year/{id}/destroy', [StudyAcademicYearController::class,'destroy']);



	Route::get('program-module-assignment-requests', [ProgramModuleAssignmentRequestController::class,'index']);


	Route::get('program-module-assignments', [ProgramModuleAssignmentController::class,'index']);
	Route::get('program-module-assignment/{ac_year_id}/{campus_prog_id}/assign', [ProgramModuleAssignmentController::class,'assignModules']);
	Route::get('program-module-assignment/{ac_year_id}/{campus_prog_id}/assign-as-previous', [ProgramModuleAssignmentController::class,'assignPreviousModules']);
	Route::post('program-module-assignment/store',[ProgramModuleAssignmentController::class,'store']);
    Route::post('program-module-assignment/update',[ProgramModuleAssignmentController::class,'update']);
    Route::get('program-module-assignment/{id}/destroy', [ProgramModuleAssignmentController::class,'destroy']);


    Route::get('options-allocations',[ProgramModuleAssignmentController::class,'allocateOptions']);
    Route::post('allocate-options',[ProgramModuleAssignmentController::class,'allocateStudentOptions']);

	Route::get('examinations', [ExaminationController::class,'index']);
	Route::post('examination/store', [ExaminationController::class,'store']);
	Route::post('examination/update', [ExaminationController::class,'update']);
	Route::get('examination/{id}/destroy', [ExaminationController::class,'destroy']);


	Route::get('examination-policies', [ExaminationPolicyController::class,'index']);
	Route::post('examination-policy/store', [ExaminationPolicyController::class,'store']);
	Route::post('examination-policy/update', [ExaminationPolicyController::class,'update']);
	Route::get('examination-policy/{id}/destroy', [ExaminationPolicyController::class,'destroy']);
	Route::get('examination-policy/{ac_yr_id}/assign-as-previous',[GradingPolicyController::class, 'assignPreviousPolicies']);


	Route::get('grading-policies', [GradingPolicyController::class,'index']);
	Route::post('grading-policy/store', [GradingPolicyController::class,'store']);
	Route::post('grading-policy/update', [GradingPolicyController::class,'update']);
	Route::get('grading-policy/{id}/destroy', [GradingPolicyController::class,'destroy']);
	Route::get('grading-policy/{ac_yr_id}/assign-as-previous',[GradingPolicyController::class, 'assignPreviousPolicies']);


	Route::get('postponements', [PostponementController::class,'index']);
	Route::post('postponement/store', [PostponementController::class,'store']);
	Route::post('postponement/update', [PostponementController::class,'update']);
	Route::get('postponement/{id}/destroy', [PostponementController::class,'destroy']);
	Route::post('postponement/recommend', [PostponementController::class,'recommend']);
	Route::get('postponement/{id}/accept', [PostponementController::class,'accept']);
	Route::get('postponement/{id}/decline', [PostponementController::class,'decline']);
	Route::get('postponement/{id}/recommend', [PostponementController::class,'showRecommend']);
	Route::post('accept-postponements',[PostponementController::class,'acceptPostponements']);
	Route::post('accept-resumptions',[PostponementController::class,'acceptResumptions']);
	Route::get('postponement/resumptions',[PostponementController::class,'resumptions']);
	Route::get('postponement/{id}/resume/recommend',[PostponementController::class,'showResumeRecommend']);
	Route::post('postponement/resumption/recommend',[PostponementController::class,'resumeRecommend']);
	Route::get('postponement/{id}/resume',[PostponementController::class,'resumePostponement']);



	Route::post('examination-irregularity/store', [ExaminationIrregularityController::class,'store']);
	Route::post('examination-irregularity/update', [ExaminationIrregularityController::class,'update']);
	Route::get('examination-irregularity/{id}/destroy', [ExaminationIrregularityController::class,'destroy']);


	Route::get('awards', [AwardController::class,'index']);
	Route::post('award/store', [AwardController::class,'store']);
	Route::post('award/update', [AwardController::class,'update']);
	Route::get('award/{id}/destroy', [AwardController::class,'destroy']);


	Route::get('streams', [StreamController::class,'index']);
	Route::get('stream-reset', [StreamController::class,'resetStreams']);
	Route::post('stream/store', [StreamController::class,'store']);
	Route::get('stream/{id}/destroy', [StreamController::class,'destroy']);
    Route::get('stream/{id}/attendance', [StreamController::class,'showAttendance']);

	Route::get('stream-components', [StreamComponentController::class,'index']);
	Route::post('stream-component/store', [StreamComponentController::class,'store']);
	Route::get('stream-component/{id}/destroy', [StreamComponentController::class,'destroy']);


	Route::get('special-exams', [SpecialExamController::class,'index']);
	Route::post('special-exam/store', [SpecialExamController::class,'store']);
	Route::post('special-exam/update', [SpecialExamController::class,'update']);
	Route::get('special-exam/{id}/destroy', [SpecialExamController::class,'destroyRequest']);
	Route::get('special-exam/{id}/accept', [SpecialExamController::class,'accept']);
	Route::get('special-exam/{id}/decline', [SpecialExamController::class,'decline']);
	Route::post('special-exam/recommend', [SpecialExamController::class,'recommend']);
	Route::get('special-exam/{id}/recommend', [SpecialExamController::class,'showRecommend']);
	Route::post('accept-special-exams',[SpecialExamController::class,'acceptSpecialExams']);


	Route::get('results', [ExaminationResultController::class,'showProcess']);
	Route::post('results/process',[ExaminationResultController::class,'process']);
	Route::get('results/uploaded-modules',[ExaminationResultController::class,'showUploadedModules']);
	Route::get('results/uploaded-modules/{id}/students',[ExaminationResultController::class,'showUploadedModuleStudents']);
	Route::get('results/show-program-results',[ExaminationResultController::class,'showProgramResults']);
	Route::get('results/show-module-results',[ExaminationResultController::class,'showModuleResults']);
	Route::get('results/show-student-results',[ExaminationResultController::class,'showStudentResults']);
	Route::get('results/student-mark-editing',[CourseWorkResultController::class,'markEdit']);
	Route::get('results/post-mark-edit',[CourseWorkResultController::class,'postMarkEdit']);
	Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/show-student-results',[ExaminationResultController::class,'showStudentAcademicYearResults']);
	Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/show-student-overall-results',[ExaminationResultController::class,'showStudentOverallResults']);
	Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/show-student-perfomance-report',[ExaminationResultController::class,'showStudentPerfomanceReport']);
	Route::get('results/{student_id}/{mod_assign_id}/{exam_id}/edit-course-work-results',[CourseWorkResultController::class,'edit']);
	Route::post('results/update-course-work-results',[CourseWorkResultController::class,'update']);
	Route::get('results/{student_id}/{ac_yr_id}/{prog_id}/edit-student-results',[ExaminationResultController::class,'edit']);
	Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/{semester_id}/add-student-results',[ExaminationResultController::class,'create']);
	Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/process-student-results',[ExaminationResultController::class,'processStudentResults']);
	Route::get('results/{module_id}/{student_id}/{ac_yr_id}/{yr_of_study}/process-student-examination-results',[ExaminationResultController::class,'updateStudentResults']);

	Route::get('results/upload-module-results',[ExaminationResultController::class, 'uploadModuleResults']);
	Route::post('results/update-examination-results',[ExaminationResultController::class, 'update']);
	Route::post('results/update-examination-results-appeal',[ExaminationResultController::class, 'updateAppeal']);
	Route::post('results/store-examination-results',[ExaminationResultController::class, 'store']);
	Route::post('results/show-program-report',[ExaminationResultController::class,'showProgramResultsReport']);
	Route::post('results/show-module-report',[ExaminationResultController::class,'showModuleResultsReport']);
	Route::get('results/show-student-report',[ExaminationResultController::class,'showStudentResultsReport']);
	Route::get('results/show-results-upload',[ExaminationResultController::class,'showResultsUpload']);
	Route::get('results/examination-irregularities',[ExaminationIrregularityController::class,'showProgramModuleIrregularities']);
	Route::get('results/program-module-assignment/{ac_yr_id}/{prog_id}/examination-irregularities',[ExaminationIrregularityController::class,'assignIrregularities']);



    Route::post('group/store', [GroupController::class,'store']);
	Route::get('group/{id}/destroy', [GroupController::class,'destroy']);
	Route::get('group/{id}/attendance', [GroupController::class,'showAttendance']);


	Route::get('results-publications', [ResultPublicationController::class,'index']);
	Route::post('result-publication/store', [ResultPublicationController::class,'store']);
	Route::post('result-publication/update', [ResultPublicationController::class,'update']);
	Route::get('result-publication/{id}/destroy', [ResultPublicationController::class,'destroy']);
	Route::get('result-publication/{id}/publish', [ResultPublicationController::class,'publish']);
	Route::get('result-publication/{id}/unpublish', [ResultPublicationController::class,'unpublish']);

	Route::get('clearance',[ClearanceController::class,'showList']);
	Route::post('clearance/update',[ClearanceController::class,'update']);
	Route::post('bulk-clearance',[ClearanceController::class,'clearBulk']);

	Route::get('run-graduants',[GraduantController::class,'runGraduants']);
	Route::post('graduants/sort',[GraduantController::class,'sortGraduants']);
	Route::get('graduants',[GraduantController::class,'showGraduants']);
	Route::get('excluded-graduants',[GraduantController::class,'showExcludedGraduants']);
	Route::post('approve-graduants',[GraduantController::class,'approveGraduants']);


	Route::get('results/appeals',[AppealController::class,'index']);

	Route::get('results/global-report',[ExaminationResultController::class,'showGlobalReport']);
	Route::post('results/get-global-report',[ExaminationResultController::class,'getGlobalReport']);

	Route::get('transcript/{student_id}',[ExaminationResultController::class,'showStudentTranscript']);
    Route::get('transcript-requests',[TranscriptRequestController::class,'index']);

    Route::get('download-appeal-list',[AppealController::class,'downloadAppealList']);
    Route::post('upload-appeal-list',[AppealController::class,'uploadAppealList']);
   

    Route::get('performance-report-requests',[PerformanceReportRequestController::class,'index']);
    Route::get('performance-report/ready',[PerformanceReportRequestController::class,'ready']);
    Route::get('statement-of-results/{student_id}',[ExaminationResultController::class,'showStudentStatementOfResults']);

    Route::get('download-graduant-list',[GraduantController::class,'downloadList']);
    Route::get('download-graduant-list-cert',[GraduantController::class,'downloadCertList']);

    Route::get('enrollment-report',[GraduantController::class,'enrollmentReport']);
    Route::get('submit-enrolled-students',[GraduantController::class,'submitEnrolledStudents']);
    Route::get('download-enrolled-students',[GraduantController::class,'downloadEnrolledStudents']);

    Route::get('student-search',[StudentController::class, 'searchForStudent']);
    Route::get('student-profile',[StudentController::class,'showStudentProfile']);
    Route::get('student-program-module-assignment/{id}',[ProgramModuleAssignmentController::class,'showOptionalStudents']);

    Route::get('get-program-by-code',[ProgramController::class,'getByCode']);
    Route::get('download-opted-students',[ProgramModuleAssignmentController::class, 'downloadOptedStudents']);

    Route::get('special-case-students',[StudentController::class,'specialCaseStudents']);
});
