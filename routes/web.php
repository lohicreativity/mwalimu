<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\GePG\BillController;
use App\Http\Controllers\GePG\ConsumerController;
use App\Http\Controllers\GePG\GePGController;
use App\Http\Controllers\GePG\GePGResponseController;
use Illuminate\Support\Facades\Http;
use App\Services\ACPACService;
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

Route::get('test',function(){
	// $payment = App\Domain\Finance\Models\NactePayment::latest()->first();
	// $result = Illuminate\Support\Facades\Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/payment/'.$payment->reference_no.'/JKb6229cfce105c6.0fb7aaa46fe8bc757813ab7f5391c58d90f891e4c86badb055b90896b8206d33.4160cea2b30cf96a8977d2de8141a655213b737d');

	// $response = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/particulars/NS1198.0038.2009-4/'.config('constants.NACTE_API_KEY'));

	// return $response;

	// return $result;
	$acpac = new ACPACService;
	//$acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES
   //('J','CRDB','REC03','10','TF','MNMA003','TEST','INV003','100.0','C','10')");
	$acpac->query("DELETE FROM customer");
	$acpac->query("DELETE FROM invoices");
	$acpac->query("DELETE FROM receipts");
	$results = $acpac->query('SELECT * FROM receipts');
    while ($row = sqlsrv_fetch_array($results)) {
    	print_r($row);
    }
});

Route::get('rec',[GePGResponseController::class,'getReceipt']);

Route::view('/', 'auth.login');

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', [HomeController::class,'dashboard'])->name('dashboard');


Route::post('/bills/post_bill', [BillController::class,'store']);
Route::delete('/bills/{bill_id}', [BillController::class,'destroy']);
# Reconciliation
Route::post('/bills/reconcile', [BillController::class,'postReconciliation']);

# Consumers
Route::get('/consumers/post_bill_to_gepg', [ConsumerController::class,'postBill']);
Route::get('/consumers/post_reconciliation_to_gepg', [ConsumerController::class,'postReconciliation']);
Route::get('/consumers/post_controlno_to_sp', [ConsumerController::class,'postControlNo']);
Route::get('/consumers/post_receipt_to_sp', [ConsumerController::class,'postReceipt']);
Route::get('/consumers/post_reconciliation_to_sp', [ConsumerController::class,'postRecon']);

# GePG
Route::post('/gepg/bill', [GePGController::class,'getBill']);
Route::post('/gepg/receipt', [GePGController::class,'getReceipt']);
Route::post('/gepg/reconcile', [GePGController::class,'getReconciliation']);

Route::post('/response/gepg/bill', [GePGResponseController::class,'getBill']);
Route::post('/response/gepg/receipt', [GePGResponseController::class,'getReceipt']);
Route::post('/response/gepg/reconcile', [GePGResponseController::class,'getReconciliation']);

Route::middleware(['auth:sanctum', 'verified'])->group(function(){
     Route::get('change-password',[SessionController::class, 'changePassword']);
     Route::post('update-password',[SessionController::class, 'update']);
});