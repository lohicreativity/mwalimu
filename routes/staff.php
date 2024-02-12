<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StaffController;

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

	Route::get('staff-members', [StaffController::class,'index'])->name('staff-members');
	Route::get('staff/create', [StaffController::class,'create']);
	Route::get('staff/{id}/edit', [StaffController::class,'edit']);
	Route::get('staff/{id}/show', [StaffController::class,'show']);
	Route::post('staff/store', [StaffController::class,'store']);
	Route::post('staff/update', [StaffController::class,'update']);
	Route::post('staff/update-roles', [StaffController::class,'updateRoles']);
	Route::post('staff/update-details', [StaffController::class,'updateDetails']);
	Route::get('staff/{id}/destroy', [StaffController::class,'destroy']);
	Route::get('reset-password-default', [StaffController::class,'resetPasswordDefaulty']);
});
