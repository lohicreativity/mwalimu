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
use App\Http\Controllers\ModuleAssignmentController;
use App\Http\Controllers\AssessmentPlanController;
use App\Http\Controllers\ProgramModuleAssignmentController;

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


	Route::get('module-assignments', [ModuleAssignmentController::class,'index']);
	Route::post('module-assignment/store', [ModuleAssignmentController::class,'store']);
	Route::get('module-assignment/{id}/destroy', [ModuleAssignmentController::class,'destroy']);


	Route::get('staff-module-assignments', [ModuleAssignmentController::class,'showStaffAssignedModules']);
	Route::get('staff-module-assignment/{id}/assessment-plans', [ModuleAssignmentController::class,'showAssessmentPlans']);
	Route::get('staff-module-assignment/{id}/syllabus', [ModuleAssignmentController::class,'showSyllabus']);
	Route::get('staff-module-assignment/{id}/module-attendance', [ModuleAssignmentController::class,'showModuleAttendance']);
	Route::get('staff-module-assignment/{id}/results', [ModuleAssignmentController::class,'showResultsUpload']);
	Route::post('staff-module-assignment/{id}/results/compute-course-work', [ModuleAssignmentController::class,'computeCourseWork']);
	Route::post('staff-module-assignment/results/compute-results', [ModuleAssignmentController::class,'computeResults']);
	Route::get('staff-module-assignment/{id}/results/download-course-work', [ModuleAssignmentController::class,'downloadCourseWork']);


	Route::post('assessment-plan/store',[AssessmentPlanController::class,'store']);
	Route::post('assessment-plan/update',[AssessmentPlanController::class,'update']);


	Route::get('campuses', [CampusController::class,'index']);
	Route::post('campus/store', [CampusController::class,'store']);
	Route::post('campus/update', [CampusController::class,'update']);
	Route::get('campus/{id}/destroy', [CampusController::class,'destroy']);


	Route::get('academic-years', [AcademicYearController::class,'index']);
	Route::post('academic-year/store', [AcademicYearController::class,'store']);
	Route::post('academic-year/update', [AcademicYearController::class,'update']);
	Route::get('academic-year/{id}/destroy', [AcademicYearController::class,'destroy']);

	Route::get('study-academic-years', [StudyAcademicYearController::class,'index']);
	Route::post('study-academic-year/store', [StudyAcademicYearController::class,'store']);
	Route::post('study-academic-year/update', [StudyAcademicYearController::class,'update']);
	Route::get('study-academic-year/{id}/destroy', [StudyAcademicYearController::class,'destroy']);

	Route::get('academic-year-programs', [AcademicYearController::class,'showPrograms']);
	Route::post('academic-year-programs/update', [AcademicYearController::class,'updatePrograms']);


	Route::get('program-module-assignments', [ProgramModuleAssignmentController::class,'index']);
	Route::post('program-module-assignments/search', [ProgramModuleAssignmentController::class,'searchAcademicYear']);
	Route::post('program-module-assignments/update', [ProgramModuleAssignmentController::class,'update']);


	Route::get('examinations', [ExaminationController::class,'index']);
	Route::post('examination/store', [ExaminationController::class,'store']);
	Route::post('examination/update', [ExaminationController::class,'update']);
	Route::get('examination/{id}/destroy', [ExaminationController::class,'destroy']);


	Route::get('awards', [AwardController::class,'index']);
	Route::post('award/store', [AwardController::class,'store']);
	Route::post('award/update', [AwardController::class,'update']);
	Route::get('award/{id}/destroy', [AwardController::class,'destroy']);

});
