<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\FeeItemController;
use App\Http\Controllers\FeeAmountController;
use App\Http\Controllers\ProgramFeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NactePaymentController;
use App\Http\Controllers\LoanAllocationController;
use App\Http\Controllers\ACPACController;
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
	Route::get('payer-details', [StaffController::class,'viewPayerDetails']);
	Route::post('request-control-number', [StaffController::class,'initiateControlNumberRequest']);
	Route::get('show-control-number', [StaffController::class,'showControlNumber']);	
	
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
	Route::get('fee-amount/assign-as-previous',[FeeAmountController::class,'assignAsPrevious']);

	Route::get('program-fees', [ProgramFeeController::class,'index'])->name('fee-items');
	Route::post('program-fee/store', [ProgramFeeController::class,'store']);
	Route::post('program-fee/update', [ProgramFeeController::class,'update']);
	Route::get('program-fee/{id}/structure', [ProgramFeeController::class,'feeStructure']);
	Route::get('program-fee/{id}/destroy', [ProgramFeeController::class,'destroy']);
	Route::get('program-fee/store-as-previous',[ProgramFeeController::class,'storeAsPrevious']);

	Route::get('payments',[PaymentController::class, 'index']);
	Route::get('payment/{id}/distributions',[PaymentController::class, 'showDistributions']);

    Route::get('nacte-payments',[NactePaymentController::class,'index']);
    Route::post('nacte-payment/store', [NactePaymentController::class,'store']);
	Route::post('nacte-payment/update', [NactePaymentController::class,'update']);
	Route::get('nacte-payment/{id}/destroy', [NactePaymentController::class,'destroy']);

	Route::get('loan-allocations',[LoanAllocationController::class,'index']);
	Route::get('doanload-loan-allocation-template',[LoanAllocationController::class,'downloadAllocationTemplate']);	
	Route::post('upload-loan-allocation',[LoanAllocationController::class,'uploadAllocations']);
	Route::get('loan-beneficiaries',[LoanAllocationController::class,'showLoanBeneficiaries']);
	Route::get('update-loan-beneficiaries',[LoanAllocationController::class,'updateLoanBeneficiaries']);	
	Route::get('loan-bank-details',[LoanAllocationController::class,'showLoanBankDetails']);
	Route::post('loan-allocation-update-signatures',[LoanAllocationController::class,'updateSignatures']);
	Route::get('notify-loan-students',[LoanAllocationController::class,'notifyLoanStudents']);

	Route::get('invoices',[ACPACController::class,'invoices']);
	Route::get('receipts',[ACPACController::class,'receipts']);
	Route::get('download-invoices',[ACPACController::class,'downloadInvoices']);
	Route::get('download-receipts',[ACPACController::class,'downloadReceipts']);
});


Route::get('get-reconcile',[InvoiceController::class,'showReconcile']);
	Route::post('post-reconcile',[InvoiceController::class,'postReconcile']);
	Route::post('post-reconciliation',[InvoiceController::class,'postReconciliation']);
