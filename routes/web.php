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
	// $acpac = new ACPACService;
	// //$acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES
 //   //('J','CRDB','REC03','10','TF','MNMA003','TEST','INV003','100.0','C','10')");
	// $acpac->query("DELETE FROM customer");
	// $acpac->query("DELETE FROM invoices");
	// $acpac->query("DELETE FROM receipts");
	// $results = $acpac->query('SELECT * FROM receipts');
 //    while ($row = sqlsrv_fetch_array($results)) {
 //    	print_r($row);
 //    }

	// return public_path('uploads/'); 31083
	$gatepay = GatewayPayment::find(31973);
	
	
	$invoice = Invoice::with('feeType')->where('control_no',$gatepay->control_no)->first();
		//$invoice->gateway_payment_id = $gatepay->id;
		//$invoice->save();

		$acpac = new ACPACService;
		if($invoice->payable_type == 'applicant'){
			$applicant = Applicant::find($invoice->payable_id);
			$stud_name = $applicant->surname.', '.$applicant->first_name.' '.$applicant->middle_name;
			$stud_reg = 'NULL';
			if(str_contains($invoice->feeType->name,'Application Fee')){
			   $applicant->payment_complete_status = 1;
			   $applicant->save();

			   //$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);
               $inv =  DB::table('invoices')->select(DB::raw('invoices.*,gateway_payments.*,fee_types.*'))
			             ->join('gateway_payments','invoices.control_no','=','gateway_payments.control_no')
						 ->join('fee_types','invoices.fee_type_id','=','fee_types.id')
						 ->where('invoices.id',$invoice->id)
						 ->first();

		        if($inv->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");
		    }

			

			if(str_contains($invoice->feeType->name,'Tuition Fee')){
				$paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
				$percentage = $paid_amount/$invoice->amount;
				$applicant = Applicant::with('applicationWindow')->find($invoice->payable_id);

				$ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
		    	$study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
		    		   $query->where('year','LIKE','%'.$ac_year.'/%');
		    	})->first();

		    	if($study_academic_year){
		    		$loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$study_academic_year->id)->first();
		    	}else{
		    		$loan_allocation = null;
		    	}			

                if($loan_allocation){
                   $percentage = ($paid_amount+$loan_allocation->tuition_fee)/$invoice->amount;
                   $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
                }else{
			       $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
			    }
			    $applicant->save();
			}

			if(str_contains($invoice->feeType->name,'Miscellaneous')){
				$applicant = Applicant::find($invoice->payable_id);
			    $applicant->other_payment_check = $data['paid_amount'] == $invoice->amount? 1 : 0;
			    $applicant->save();
			}
			
		}

		if($invoice->payable_type == 'student'){
			if(str_contains($invoice->feeType->name,'Appeal')){
				 Appeal::where('student_id',$invoice->payable_id)->where('invoice_id',$invoice->id)->update(['is_paid'=>1]);
			}

			if(str_contains($invoice->feeType->name,'Performance Report')){
				 PerfomanceReportRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID']);
			}

			if(str_contains($invoice->feeType->name,'Transcript')){
				 TranscriptRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID']);
			}

			$student = Student::find($invoice->payable_id);

			$stud_name = $student->surname.', '.$student->first_name.' '.$student->middle_name;
	        $stud_reg = substr($student->registration_number, 5);
	        $stud_reg = str_replace('/', '', $stud_reg);
	        $parts = explode('.', $stud_reg);
	        if($parts[0] == 'BTC'){
	            $stud_reg = 'BT'.$parts[1];
	        }else{
	            $stud_reg = $parts[0].$parts[1];
	        }

	        if($student->registration_year >= 2022){
                //$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);
				$inv =  DB::table('invoices')->select(DB::raw('invoices.*,gateway_payments.*,fee_types.*'))
			             ->join('gateway_payments','invoices.control_no','=','gateway_payments.control_no')
						 ->join('fee_types','invoices.fee_type_id','=','fee_types.id')
						 ->where('invoices.id',$invoice->id)
						 ->first();
						 
			    return $inv;

				$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$inv->control_no."','".date('Y',strtotime($inv->created_at))."','".$inv->description."','".$stud_reg."','".$stud_name."','1','".$inv->gl_code."','".$inv->name."','".$inv->description."','".$inv->amount."','0','".date('Y',strtotime(now()))."')");

		        if($inv->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

	        }else{
               //$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);
			   $inv =  DB::table('invoices')->select(DB::raw('invoices.*,gateway_payments.*,fee_types.*'))
			             ->join('gateway_payments','invoices.control_no','=','gateway_payments.control_no')
						 ->join('fee_types','invoices.fee_type_id','=','fee_types.id')
						 ->where('invoices.id',$invoice->id)
						 ->first();

		        if($inv->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $stud_reg = 'NULL';

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");
	        }
		}
		
		return 'Done';
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