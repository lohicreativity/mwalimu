<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthInsuranceController;
use App\Http\Controllers\SpecialDateController;

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
    
    Route::post('verify-nhif',[HealthInsuranceController::class,'verifyNHIF']);
    Route::post('store-other-card',[HealthInsuranceController::class,'storeOtherCard']);
    Route::post('request-nhif',[HealthInsuranceController::class,'requestNHIF']);

    Route::get('registration-deadline',[SpecialDateController::class,'showRegistrationDeadline']);
    Route::post('store-registration-deadline',[SpecialDateController::class,'storeRegistrationDeadline']);
    Route::post('update-registration-deadline',[SpecialDateController::class,'updateRegistrationDeadline']);

    Route::get('orientation-date',[SpecialDateController::class,'showOrientationDate']);
    Route::post('store-orientation-date',[SpecialDateController::class,'storeOrientationDate']);
    Route::post('update-orientation-date',[SpecialDateController::class,'updateOrientationDate']);

});


