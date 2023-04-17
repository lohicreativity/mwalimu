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

use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\PerfomanceReportRequest;
use App\Domain\Academic\Models\TranscriptRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Finance\Models\PaymentReconciliation;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\ApplicantProgramSelection;


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

Route::get('batch-processing', function (Request $request) {
    //$applicant = Applicant::where('index_number',$data['f4indexno'])->where('application_window_id', $request->get('application_window_id'))
	//						->where('program_level_id',$request->get('program_level_id'))->first(); // from API
     $batch = ApplicantProgramSelection::whereHas('applicant',function($query) use($request){
	 $query->where('program_level_id',1);})
	 ->where('application_window_id', 2)->where('status', 'SELECTED')->latest()->first();
	      
	$current_batch = $batch->batch_no + 1;
	
	$applicant = Applicant::where('index_number','S0836/0008/2019')->where('application_window_id', 2)
							->where('program_level_id',1)->first(); //Imitation of the previous statement
	if($applicant){
//	   $applicant->multiple_admissions = $data['AdmissionStatusCode'] == 225 ? 1 : 0; // from API
	   $applicant->multiple_admissions = 1; //Imitation of the previous statement
	   $applicant->save();

	   ApplicantProgramSelection::where('applicant_id',$applicant->id)->where('status','APPROVING')->update(['status'=>'SELECTED']);
	}

    ApplicantProgramSelection::whereHas('applicant',function($query) use($request){$query->where('program_level_id',1);})
							 ->where('application_window_id', 2)->where('batch_no', 0)->update(['batch_no' => $current_batch]);
     
	Applicant::where('application_window_id', 2)->where(function($query) {$query->where('status', null)->orWhere('status', 'SELECTED');})
			 ->where('program_level_id',1)->where('batch_no', 0)->update(['batch_no' => $current_batch]);


});

Route::get('test',function(){
	// $payment = App\Domain\Finance\Models\NactePayment::latest()->first();
	// $result = Illuminate\Support\Facades\Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/payment/'.$payment->reference_no.'/JKb6229cfce105c6.0fb7aaa46fe8bc757813ab7f5391c58d90f891e4c86badb055b90896b8206d33.4160cea2b30cf96a8977d2de8141a655213b737d');

	// $response = Http::get('https://www.nacte.go.tz/nacteapi/index.php/api/particulars/NS1198.0038.2009-4/'.config('constants.NACTE_API_KEY'));

	// return $response;

	// return $result;
	// $acpac = new ACPACService;

	// //$acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES
 //   //('J','CRDB','REC03','10','TF','MNMA003','TEST','INV003','100.0','C','10')");
     //$acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('BDED485922','44322','SHOBOLE, JOVITH ','P.O Box 27,Simiyu','ARUMERU','BANG','Unknown','Tanzania','Tanzania','P.O Box 27,Simiyu','Tanzania','Jones, Shobole Nyombi','255753690473','0787691417','BD.ED','STD','TSH','dennis.lupiana@gmail.com','UNKNOWN')");
	

	// $acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('BTCOD001422','CCOD9','MPAMBA, OTHUMAN S','fddgdgfd','KIGOMA','MATENDO','','KIGOMA','Tanzania','fddgdgfd','Tanzania','dsfdsafsdf, ddgfdsff ','255746508500','255746508500','','STD','TSH','dennis.lupiana@gmail.com','UNKNOWN')");

	// INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('BTCOD00122','12221','MPAMBA, OTHUMAN S','fddgdgfd','KIGOMA','MATENDO','qwerty','KIGOMA','Tanzania','fddgdgfd','Tanzania','dsfdsafsdf, ddgfdsff ','255746508500','255746508500','BTC.COD','STD','TSH','dennis.lupiana@gmail.com','UNKNOWN')


	// $acpac->query("DELETE FROM customer");
	// $acpac->query("DELETE FROM invoices");
	// $acpac->query("DELETE FROM receipts");
/* 	$results = $acpac->query('SELECT * FROM customer');
     while ($row = sqlsrv_fetch_array($results)) {
     	print_r($row);
    }

    echo '<br><br><br>';

    $results = $acpac->query('SELECT * FROM invoices');
     while ($row = sqlsrv_fetch_array($results)) {
     	print_r($row);
    }

    echo '<br><br><br>';

    $results = $acpac->query('SELECT * FROM receipts');
     while ($row = sqlsrv_fetch_array($results)) {
     	print_r($row);
    } */

	//$acpac->query("INSERT INTO customer (IDCUST,IDGRP,NAMECUST,TEXTSTRE1,TEXTSTRE2,TEXTSTRE3,TEXTSTRE4,NAMECITY,CODESTTE,CODEPSTL,CODECTRY,NAMECTAC,TEXTPHON1,TEXTPHON2,CODETERR,IDACCTSET,CODECURN,EMAIL1,EMAIL2) VALUES ('BTBA000122','16722','LEOPOLD, LEONTINE ','ILEMELA','ARUMERU','BANG','9193 DSM','Tanzania','Tanzania','ILEMELA','Tanzania','NGWARA, NYAMBOHA KIKARO','255754991909','0754991909','BTC.BA','STD','TSH','yusufu.erick@mnma.ac.tz','leontine97@gmail.com')");
	// $acpac->query("DELETE FROM customer");
	// $acpac->query("DELETE FROM invoices");
	// $acpac->query("DELETE FROM receipts");
	// $results = $acpac->query('SELECT * FROM receipts');
 //     while ($row = sqlsrv_fetch_array($results)) {
 //     	print_r($row);
 //     }


	 // $gatepay = GatewayPayment::find(349072);
	 

	 // $invoice = Invoice::with('feeType')->where('control_no',$gatepay->control_no)->first();

  //       if($invoice->payable_type == 'applicant'){
  //           $applicant = Applicant::find($invoice->payable_id);
  //           $stud_name = $applicant->surname.', '.$applicant->first_name.' '.$applicant->middle_name;
  //           $stud_reg = 'NULL';
  //           if(str_contains($invoice->feeType->name,'Application Fee')){
  //              $applicant->payment_complete_status = 1;
  //              $applicant->save();
               
  //           }

  //           if(str_contains($invoice->feeType->name,'Tuition Fee')){
  //               $paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
  //               $percentage = $paid_amount/$invoice->amount;
  //               $applicant = Applicant::with('applicationWindow')->find($invoice->payable_id);

  //               $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
  //               $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
  //                      $query->where('year','LIKE','%'.$ac_year.'/%');
  //               })->first();

  //               if($study_academic_year){
  //                   $loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$study_academic_year->id)->first();
  //               }else{
  //                   $loan_allocation = null;
  //               }           

  //               if($loan_allocation){
  //                  $percentage = ($paid_amount+$loan_allocation->tuition_fee)/$invoice->amount;
  //                  $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
  //               }else{
  //                  $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
  //               }
  //               $applicant->save();
  //           }

  //           if(str_contains($invoice->feeType->name,'Miscellaneous')){
  //               $applicant = Applicant::find($invoice->payable_id);
  //               $applicant->other_payment_check = $data['paid_amount'] == $invoice->amount? 1 : 0;
  //               $applicant->save();
  //           }
            
  //       }

  //       if($invoice->payable_type == 'student'){
  //           if(str_contains($invoice->feeType->name,'Appeal')){
  //                Appeal::where('student_id',$invoice->payable_id)->where('invoice_id',$invoice->id)->update(['is_paid'=>1]);
  //           }

  //           if(str_contains($invoice->feeType->name,'Performance Report')){
  //                PerfomanceReportRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID','status'=>'PENDING']);
  //           }

  //           if(str_contains($invoice->feeType->name,'Transcript')){
  //                TranscriptRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID']);
  //           }

  //       }

		//dispatch(new UpdateGatewayPayment($gatepay));
	   // return 'Done';
	 
	 
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