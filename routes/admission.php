<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdmissionController;

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

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('payments',[AdmissionController::class,'payments']);

    Route::get('admission-confirmation',[AdmissionController::class,'confirmSelection']);

    Route::post('request-control-number',[AdmissionController::class,'requestPaymentControlNumber']);
});
