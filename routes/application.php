<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\NextOfKinController;
use App\Http\Controllers\ApplicationWindowController;
use App\Http\Controllers\NectaResultController;
use App\Http\Controllers\NacteResultController;
use App\Http\Controllers\ResultsRequests\NECTAServiceController;
use App\Http\Controllers\ResultsRequests\NACTEServiceController;

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
Route::get('/', [ApplicationController::class,'index']);
Route::get('registration', [ApplicationController::class,'index']);
Route::post('registration/store',[ApplicationController::class,'store']);
Route::get('login',[ApplicantController::class,'showLogin']);
Route::post('authenticate',[ApplicantController::class,'authenticate']);




Route::middleware(['auth:sanctum', 'verified'])->group(function(){

	Route::get('dashboard',[ApplicantController::class,'editBasicInfo']);
	Route::get('basic-information',[ApplicantController::class,'editBasicInfo']);
	Route::get('next-of-kin',[ApplicantController::class,'editNextOfKin']);
	Route::get('payments',[ApplicantController::class,'payments']);
	Route::get('results',[ApplicantController::class,'requestResults']);
	Route::get('select-programs',[ApplicantController::class,'selectPrograms']);
	Route::get('upload-documents',[ApplicantController::class,'uploadDocuments']);
	Route::get('submission',[ApplicantController::class,'submission']);

	Route::post('update-basic-info',[ApplicantController::class,'updateBasicInfo']);
	Route::post('next-of-kin/store',[NextOfKinController::class,'store']);
	Route::post('next-of-kin/update',[NextOfKinController::class,'update']);
	Route::post('upload-documents',[ApplicationController::class,'uploadDocuments']);
	Route::get('delete-document',[ApplicationController::class,'deleteDocument']);
	Route::post('store-program-selection',[ApplicantController::class,'storeProgramSelection']);
	Route::get('reset-program-selection/{id}',[ApplicationController::class,'resetProgramSelection']);
	Route::post('request-control-number',[ApplicationController::class,'getControlNumber']);
	Route::post('program/select',[ApplicationController::class,'selectProgram']);
	Route::post('submit-application',[ApplicationController::class,'submitApplication']);


	Route::get('application-windows', [ApplicationWindowController::class,'index']);
	Route::post('application-window/store', [ApplicationWindowController::class,'store']);
	Route::post('application-window/update', [ApplicationWindowController::class,'update']);
	Route::get('application-window/{id}/destroy', [ApplicationWindowController::class,'destroy']);


	Route::get('applicants/list',[ApplicationController::class,'showApplicantsList']);
    Route::get('fetch-necta-results/{index_number}/{exam_id}',[NECTAServiceController::class,'getResults']);
    Route::get('fetch-nacte-results/{avn}',[NACTEServiceController::class,'getResults']);
    Route::post('get-necta-results',[ApplicantController::class,'getNectaResults']);
    Route::post('get-nacte-results',[ApplicantController::class,'getNacteResults']);

    Route::post('necta-result/decline',[NectaResultController::class,'destroy']);
    Route::post('nacte-result/decline',[NacteResultController::class,'destroy']);


    Route::get('application-window-campus-programs', [ApplicationWindowController::class,'showPrograms']);
	Route::post('application-window-campus-programs/update', [ApplicationWindowController::class,'updatePrograms']);
});
