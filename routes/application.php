<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\NextOfKinController;

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

	Route::get('dashboard',[ApplicantController::class,'dashboard']);
	Route::get('basic-information',[ApplicantController::class,'editBasicInfo']);
	Route::get('next-of-kin',[ApplicantController::class,'editNextOfKin']);
	Route::get('payments',[ApplicantController::class,'payments']);
	Route::get('results',[ApplicantController::class,'requestResults']);
	Route::get('eligibility',[ApplicantController::class,'checkEligibility']);
	Route::get('submission',[ApplicantController::class,'submission']);

	Route::post('update-basic-info',[ApplicantController::class,'updateBasicInfo']);
	Route::post('next-of-kin/store',[NextOfKinController::class,'store']);
	Route::post('next-of-kin/update',[NextOfKinController::class,'update']);
	Route::post('store-program-selection',[ApplicantController::class,'storeProgramSelection']);


});
