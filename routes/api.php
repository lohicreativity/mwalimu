<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\HomeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('v1/get-regions',[HomeController::class,'getRegions']);
Route::post('v1/get-districts',[HomeController::class,'getDistricts']);
Route::post('v1/get-wards',[HomeController::class,'getWards']);
Route::post('v1/get-module-by-id',[HomeController::class,'getModuleById']);
Route::post('v1/get-program-modules',[HomeController::class,'getProgramModules']);
Route::post('v1/get-program-module-assignments',[HomeController::class,'getProgramModuleAssignments']);
Route::post('v1/get-nta-level',[HomeController::class,'getNTALevel']);
Route::post('v1/get-nta-level-by-code',[HomeController::class,'getNTALevelByCode']);
Route::post('v1/get-fee-type',[HomeController::class,'getFeeType']);
Route::post('v1/get-parents',[HomeController::class,'getParents']);
Route::post('v1/get-batches',[HomeController::class,'getBatches']);
// Route::post('v1/get-faculty-parents',[HomeController::class,'getFacultyParents']);