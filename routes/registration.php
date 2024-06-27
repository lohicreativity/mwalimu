<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthInsuranceController;
use App\Http\Controllers\SpecialDateController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ApplicationController;

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

Route::middleware(['auth:sanctum', 'verified', 'checkPasswordChange'])->group(function(){
    
    Route::post('verify-nhif',[HealthInsuranceController::class,'verifyNHIF']);
    Route::post('store-other-card',[HealthInsuranceController::class,'storeOtherCard']);
    Route::post('request-nhif',[HealthInsuranceController::class,'requestNHIF']);

    Route::get('registration-deadline',[SpecialDateController::class,'showRegistrationDeadline']);
    Route::post('store-registration-deadline',[SpecialDateController::class,'storeRegistrationDeadline']);
    Route::post('update-registration-deadline',[SpecialDateController::class,'updateRegistrationDeadline']);

    Route::get('orientation-date',[SpecialDateController::class,'showOrientationDate']);
    Route::post('store-orientation-date',[SpecialDateController::class,'storeOrientationDate']);
    Route::post('update-orientation-date',[SpecialDateController::class,'updateOrientationDate']);

    Route::get('print-id-card',[RegistrationController::class,'printIDCard']);
    Route::get('printed-id-cards',[RegistrationController::class,'showPrintedIDCards']);
    Route::get('print-id-card-bulk',[RegistrationController::class,'printIDCardBulk']);
    Route::get('show-id-card-bulk',[RegistrationController::class,'showIDCardBulk']);
    Route::get('show-id-card',[RegistrationController::class,'showIDCard']);
    Route::get('compose-id-card',[RegistrationController::class,'composeIDCard']);

    Route::post('crop-student-image',[RegistrationController::class,'cropStudentImage']);

    Route::get('internal-transfer',[ApplicationController::class,'showInternalTransfer']);
    Route::post('get-internal-transfer-tcu-status',[RegistrationController::class,'getTransferVerificationStatus']);
    Route::post('submit-internal-transfer',[ApplicationController::class,'submitInternalTransfer']);

    Route::get('external-transfer',[ApplicationController::class,'showExternalTransfer']);
    Route::post('submit-external-transfers',[ApplicationController::class,'submitExternalTransfer']);
	
	Route::get('statistics',[RegistrationController::class,'statistics']);
	Route::get('active-students',[RegistrationController::class,'showActiveStudents']);
	Route::get('deceased-students',[RegistrationController::class,'showDeceasedStudents']);
	Route::get('postponed-students',[RegistrationController::class,'showPostponedStudents']);
	Route::get('unregistered-students',[RegistrationController::class,'showUnregisteredStudents']);
	Route::get('download-active-students',[RegistrationController::class,'downloadActiveStudents']);
	Route::get('download-deceased-students',[RegistrationController::class,'downloadDeceasedStudents']);
	Route::get('download-postponed-students',[RegistrationController::class,'downloadPostponedStudents']);
	Route::get('download-unregistered-students',[RegistrationController::class,'downloadUnregisteredStudents']);
});


