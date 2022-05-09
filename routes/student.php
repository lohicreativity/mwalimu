<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\AppealController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TranscriptRequestController;
use App\Http\Controllers\PostponementController;
use App\Http\Controllers\SpecialExamController;
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

	Route::get('request-transcript',[TranscriptRequestController::class, 'store']);

	Route::post('get-control-number',[InvoiceController::class,'store']);
    
    Route::get('results/{ac_year_id}/{yr_of_study}/report/appeal',[AppealController::class,'showAcademicYearResults']);

    Route::post('appeal/store',[AppealController::class,'store']);
    Route::get('request-performance-report',[PerformanceReportRequestController::class,'store']);

    Route::get('registration',[StudentController::class,'showRegistration']);
    Route::get('registration/create',[RegistrationController::class,'create']);

    Route::get('bank-information',[StudentController::class,'showBankInfo']);
    Route::post('update-bank-info',[StudentController::class,'updateBankInfo']);

    Route::get('loan-allocations',[StudentController::class,'showLoanAllocations']);

    Route::get('postponements',[StudentController::class,'requestPostponement']);

    Route::get('postponement-letter/{id}/download',[PostponementController::class,'downloadLetter']);
    Route::get('supporting-document/{id}/download',[PostponementController::class,'downloadSupportingDocument']);
    Route::get('postponement/{id}/resume',[PostponementController::class,'showResume']);
    Route::post('postponement/resume',[PostponementController::class,'submitResume']);


    Route::get('postponement/exam',[SpecialExamController::class,'showPostponement']);
    Route::post('special-exam/store',[SpecialExamController::class,'storePostponement']);

});
