<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CampusController;

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

	Route::get('semesters', [SemesterController::class,'index']);
	Route::post('semesters/store', [SemesterController::class,'store']);
	Route::post('semesters/update', [SemesterController::class,'update']);
	Route::get('semesters/{id}/destroy', [SemesterController::class,'destroy']);


	Route::get('departments', [DepartmentController::class,'index']);
	Route::post('departments/store', [DepartmentController::class,'store']);
	Route::post('departments/update', [DepartmentController::class,'update']);
	Route::get('departments/{id}/destroy', [DepartmentController::class,'destroy']);


	Route::get('programs', [ProgramController::class,'index']);
	Route::post('programs/store', [ProgramController::class,'store']);
	Route::post('programs/update', [ProgramController::class,'update']);
	Route::get('programs/{id}/destroy', [ProgramController::class,'destroy']);


	Route::get('academic-years', [AcademicYearController::class,'index']);
	Route::post('academic-years/store', [AcademicYearController::class,'store']);
	Route::post('academic-years/update', [AcademicYearController::class,'update']);
	Route::get('academic-years/{id}/destroy', [AcademicYearController::class,'destroy']);

	Route::get('academic-year-programs', [AcademicYearController::class,'showPrograms']);
	Route::post('academic-year-programs/update', [SemesterController::class,'updatePrograms']);


});
