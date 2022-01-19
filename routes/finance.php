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

	Route::get('fee-types', [FeeTypeController::class,'index'])->name('fee-types');
	Route::post('fee-type/store', [FeeTypeController::class,'store']);
	Route::post('fee-type/update', [FeeTypeController::class,'update']);
	Route::get('fee-type/{id}/destroy', [FeeTypeController::class,'destroy']);

	Route::get('fee-items', [FeeItemController::class,'index'])->name('fee-items');
	Route::post('fee-item/store', [FeeItemController::class,'store']);
	Route::post('fee-item/update', [FeeItemController::class,'update']);
	Route::get('fee-item/{id}/destroy', [FeeItemController::class,'destroy']);

	Route::get('fee-amounts', [FeeAmountController::class,'index'])->name('fee-items');
	Route::post('fee-amount/store', [FeeAmountController::class,'store']);
	Route::post('fee-amount/update', [FeeAmountController::class,'update']);
	Route::get('fee-amount/{id}/destroy', [FeeAmountController::class,'destroy']);

});
