<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthInsuranceController;

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


});


