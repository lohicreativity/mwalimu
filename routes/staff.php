<?php

use Illuminate\Support\Facades\Route;

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

	Route::get('staffs', [StaffController::class,'index'])->name('semesters');
	Route::post('staff/store', [StaffController::class,'store']);
	Route::post('staff/update', [StaffController::class,'update']);
	Route::get('staff/{id}/destroy', [StaffController::class,'destroy']);
});
