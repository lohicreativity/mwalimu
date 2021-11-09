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

	Route::get('applications', [ApplicationController::class,'index'])->name('semesters');
	Route::post('application/store', [ApplicationController::class,'store']);
	Route::post('application/update', [ApplicationController::class,'update']);
	Route::get('application/{id}/destroy', [ApplicationController::class,'destroy']);
});
