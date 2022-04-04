<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\GePG\BillController;
use App\Http\Controllers\GePG\ConsumerController;
use App\Http\Controllers\GePG\GePGController;
use App\Http\Controllers\GePG\GePGResponseController;

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
	$applicant = App\Domain\Application\Models\Applicant::has('applicationWindow')->with('applicationWindow')->first();
	$data = [
     'applicant'=>$applicant,
   ];
   $pdf = PDF::loadView('dashboard.application.reports.admission-letter',$data);
   
   $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
   $ac_year += 1;
   $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
          $query->where('year','LIKE','%'.$ac_year.'%');
    })->first();
   if(!$study_academic_year){
       return redirect()->back()->with('error','Admission study academic year not created');
   }
   $user = new User;
   $user->email = 'amanighachocha@gmail.com'; //$applicant->email;
   $user->username = $applicant->first_name.' '.$applicant->surname;
    Mail::to()->send(new App\Mail\AdminssionLetterCreated($applicant,$study_academic_year,));
});

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