<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\FeeItemController;
use App\Http\Controllers\FeeAmountController;
use App\Http\Controllers\ProgramFeeController;
use App\Http\Controllers\PaymentController;




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

	Route::get('program-fees', [ProgramFeeController::class,'index'])->name('fee-items');
	Route::post('program-fee/store', [ProgramFeeController::class,'store']);
	Route::post('program-fee/update', [ProgramFeeController::class,'update']);
	Route::get('program-fee/{id}/structure', [ProgramFeeController::class,'feeStructure']);
	Route::get('program-fee/{id}/destroy', [ProgramFeeController::class,'destroy']);

	Route::get('payments',[PaymentController::class, 'index']);

});
