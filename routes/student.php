<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\InvoiceController;

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
Route::get('login',[StudentController::class,'showLogin']);
Route::post('authenticate',[StudentController::class, 'authenticate']);
Route::get('logout',[StudentController::class, 'logout']);


Route::middleware(['auth:sanctum', 'verified'])->group(function(){
    
    Route::get('dashboard', [StudentController::class,'index']);


	Route::get('modules', [StudentController::class,'showModules']);
	Route::get('module/{id}/opt', [StudentController::class,'optModule']);
	Route::get('module/{id}/reset-option', [StudentController::class,'resetModuleOption']);

	Route::get('results',[StudentController::class, 'showResultsReport']);
    Route::get('results/{ac_year_id}/{yr_of_study}/report',[StudentController::class, 'showAcademicYearResults']);
    Route::get('results/{student_id}/{ac_yr_id}/{yr_of_study}/show-student-overall-results',[StudentController::class,'showStudentOverallResults']);

	Route::get('profile', [StudentController::class,'showProfile']);
	Route::get('payments', [StudentController::class,'showPayments']);

	Route::get('clearance',[ClearanceController::class,'index']);
	Route::post('clearance/store',[ClearanceController::class,'store']);

	Route::get('results/appeal',[AppealController::class,'appealResults']);

	Route::get('request-control-number',[StudentController::class,'requestControlNumber']);

	Route::post('get-control-number',[InvoiceController::class,'store']);
    
    Route::get('results/{ac_year_id}/{yr_of_study}/report/appeal',[AppealController::class,'showAcademicYearResults']);

    Route::post('appeal/store',[AppealController::class,'store']);

});
