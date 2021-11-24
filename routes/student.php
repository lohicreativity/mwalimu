<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

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
Route::post('authenticate',[StudentController::class, 'authenticate']);

Route::middleware(['auth:sanctum', 'verified'])->group(function(){
    
    Route::get('dashboard', [StudentController::class,'index']);


	Route::get('modules', [StudentController::class,'showModules']);
	Route::get('module/{id}/opt', [StudentController::class,'optModule']);
	Route::get('module/{id}/reset-option', [StudentController::class,'resetModuleOption']);


	Route::get('results', [StudentController::class,'showResults']);
	Route::get('profile', [StudentController::class,'showProfile']);
	Route::get('payments', [StudentController::class,'showPayments']);
});
