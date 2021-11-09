<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ModuleAssignmentController;
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


	Route::get('campuses', [CampusController::class,'index']);
	Route::post('campus/store', [CampusController::class,'store']);
	Route::post('campus/update', [CampusController::class,'update']);
	Route::get('campus/{id}/destroy', [CampusController::class,'destroy']);


	Route::get('academic-years', [AcademicYearController::class,'index']);
	Route::post('academic-year/store', [AcademicYearController::class,'store']);
	Route::post('academic-year/update', [AcademicYearController::class,'update']);
	Route::get('academic-year/{id}/destroy', [AcademicYearController::class,'destroy']);

	Route::get('academic-year-programs', [AcademicYearController::class,'showPrograms']);
	Route::post('academic-year-programs/update', [AcademicYearController::class,'updatePrograms']);


    Route::get('staff-module-assignments', [ModuleAssignmentController::class,'index']);
    Route::post('staff-module-assignments/search', [ModuleAssignmentController::class,'searchAcademicYear']);
	Route::post('staff-module-assignments/update', [ModuleAssignmentController::class,'update']);

	Route::get('program-module-assignments', [ProgramModuleAssignmentController::class,'index']);
	Route::post('program-module-assignments/search', [ProgramModuleAssignmentController::class,'searchAcademicYear']);
	Route::post('program-module-assignments/update', [ProgramModuleAssignmentController::class,'update']);


	Route::get('examinations', [ExaminationController::class,'index']);
	Route::post('examination/store', [ExaminationController::class,'store']);
	Route::post('examination/update', [ExaminationController::class,'update']);
	Route::get('examination/{id}/destroy', [ExaminationController::class,'destroy']);

});
