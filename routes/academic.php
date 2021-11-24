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
use App\Http\Controllers\AssessmentPlanController;
use App\Http\Controllers\CourseWorkComponentController;
use App\Http\Controllers\ProgramModuleAssignmentController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\StreamComponentController;
use App\Http\Controllers\ElectivePolicyController;
use App\Http\Controllers\ElectiveModuleLimitController;

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
	Route::post('module-assignment/store', [ModuleAssignmentController::class,'store']);
	Route::get('module-assignment/{id}/destroy', [ModuleAssignmentController::class,'destroy']);


	Route::get('staff-module-assignments', [ModuleAssignmentController::class,'showStaffAssignedModules']);
	Route::get('staff-module-assignment/{id}/assessment-plans', [ModuleAssignmentController::class,'showAssessmentPlans']);
	Route::get('staff-module-assignment/{id}/syllabus', [ModuleAssignmentController::class,'showSyllabus']);
	Route::get('staff-module-assignment/{id}/attendance', [ModuleAssignmentController::class,'showAttendance']);
	Route::get('staff-module-assignment/{id}/results', [ModuleAssignmentController::class,'showResultsUpload']);
	Route::get('staff-module-assignment/{id}/results/compute-course-work', [ModuleAssignmentController::class,'computeCourseWork']);
	Route::get('staff-module-assignment/results/compute-results', [ModuleAssignmentController::class,'computeResults']);
	Route::get('staff-module-assignment/{id}/results/download-course-work', [ModuleAssignmentController::class,'downloadCourseWork']);
	Route::post('module-assignment-result/store',[ModuleAssignmentController::class,'uploadResults']);


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
	Route::get('study-academic-year/{id}/destroy', [StudyAcademicYearController::class,'destroy']);


	Route::get('study-academic-year-campus-programs', [StudyAcademicYearController::class,'showPrograms']);
	Route::post('study-academic-year-campus-programs/update', [StudyAcademicYearController::class,'updatePrograms']);


	Route::get('program-module-assignments', [ProgramModuleAssignmentController::class,'index']);
	Route::get('program-module-assignment/{ac_year_id}/{campus_prog_id}/assign', [ProgramModuleAssignmentController::class,'assignModules']);
	Route::post('program-module-assignment/store',[ProgramModuleAssignmentController::class,'store']);
    Route::post('program-module-assignment/update',[ProgramModuleAssignmentController::class,'update']);
    Route::get('program-module-assignment/{id}/destroy', [ProgramModuleAssignmentController::class,'destroy']);

	Route::get('examinations', [ExaminationController::class,'index']);
	Route::post('examination/store', [ExaminationController::class,'store']);
	Route::post('examination/update', [ExaminationController::class,'update']);
	Route::get('examination/{id}/destroy', [ExaminationController::class,'destroy']);


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


    Route::post('group/store', [GroupController::class,'store']);
	Route::get('group/{id}/destroy', [GroupController::class,'destroy']);
	Route::get('group/{id}/attendance', [GroupController::class,'showAttendance']);
});
