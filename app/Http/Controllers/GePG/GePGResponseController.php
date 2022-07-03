<?php

namespace App\Http\Controllers\Gepg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use Illuminate\Support\Facades\Log;
use App\Services\ACPACService;

class GePGResponseController extends Controller
{
    /**
     * Receive bill from GePG
     */
    public function getBill(Request $request)
    {
    	$arrContextOptions=array(
		      "ssl"=>array(
		            "verify_peer"=>false,
		            "verify_peer_name"=>false,
		        ),
		    );  
		//$jsondata = file_get_contents('php://input');
		$jsondata =file_get_contents('php://input', false, stream_context_create($arrContextOptions));

		//convert json object to php associative array
		$data = json_decode($jsondata, true);

		 //get response details
		$status = $data['status'];
		$message = $data['message'];
		$bill_id = $data['data']['bill_id'];
		$control_no = $data['status'] == 1? $data['data']['control_no'] : null;

		// $bill_id = 'MNMA-1643902233';
		// $control_no = 'MNMA-1643902233';
		// $message = 'MNMA-1643902233';
		// $status = 'MNMA-1643902233';

		$invoice = Invoice::where('reference_no',$bill_id)->first();
		$invoice->control_no = $control_no;
		$invoice->message = $message;
		$invoice->status = $status;
		$invoice->save();
    }

    /**
     * Receive payment receipt from GePG
     */
    public function getReceipt(Request $request)
    {
    	$arrContextOptions=array(
		      "ssl"=>array(
		            "verify_peer"=>false,
		            "verify_peer_name"=>false,
		        ),
		    );  
    	//$jsondata = file_get_contents('php://input');
		//$data = json_decode($jsondata, true);
		$jsondata =file_get_contents('php://input', false, stream_context_create($arrContextOptions));

		$data = json_decode($jsondata, true);

		 //get response details
		$transaction_id = $data['transaction_id'];
		$sp_code = $data['sp_code'];
		$pay_refId= $data['pay_ref_id'];
		$bill_id = $data['bill_id'];
		$control_no = $data['control_no'];
		$bill_amount = $data['bill_amount'];
		$paid_amount = $data['paid_amount'];
		$bill_payOpt = $data['pay_option'];
		$ccy = $data['currency'];
		$datetime = $data['datetime'];
		$payment_channel = $data['payment_channel'];
		$cell_number = $data['cell_number'];
		$payer_email = !is_array($data['payer_email'])? $data['payer_email'] : null;
		$payer_name = $data['payer_name'];
		$psp_receipt_no = $data['psp_receipt_no'];
		$psp_name = $data['psp_name'];
        $ctry_AccNum = $data['credited_acc_num'];




		$gatepay = new GatewayPayment;
		$gatepay->transaction_id = $transaction_id;
		// $gatepay->sp_code = $sp_code;
		$gatepay->pay_refId = $pay_refId;
		$gatepay->bill_id = $bill_id;
		$gatepay->control_no = $control_no;
		$gatepay->bill_amount = $bill_amount;
		$gatepay->paid_amount = $paid_amount;
		$gatepay->bill_payOpt = $bill_payOpt;
		$gatepay->ccy = $ccy;
		$gatepay->datetime = $datetime;
		$gatepay->payment_channel = $payment_channel;
		$gatepay->cell_number = $cell_number;
		$gatepay->payer_email = $payer_email;
		$gatepay->payer_name = $payer_name;
		$gatepay->psp_receipt_no = $psp_receipt_no;
		$gatepay->ctry_AccNum = $ctry_AccNum;
		$gatepay->psp_name = $psp_name;
		$gatepay->save();


		$invoice = Invoice::with('feeType')->where('control_no',$control_no)->first();
		$invoice->gateway_payment_id = $gatepay->id;
		$invoice->save();
		if($invoice->payable_type == 'applicant'){
			$applicant = Applicant::find($invoice->payable_id);
			$applicant->payment_complete_status = 1;
			$applicant->save();

			$stud_name = $applicant->surname.', '.$applicant->first_name.' '.$applicant->middle_name;
			$stud_reg = 'NULL';

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

			$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);


		        if($inv->gatewayPayment->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($inv->gatewayPayment->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->gatewayPayment->control_no."','".$inv->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

		        Log::info("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($inv->gatewayPayment->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->gatewayPayment->control_no."','".$inv->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");


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
                $inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);


				$acpac = new ACPACService;
				$acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$inv->gatewayPayment->control_no."','".date('Y',strtotime($inv->created_at))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','1','".$inv->feeType->gl_code."','".$inv->feeType->name."','".$inv->feeType->description."','".$inv->amount."','0','".date('Y',strtotime(now()))."')");

		        if($inv->gatewayPayment->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($inv->gatewayPayment->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->gatewayPayment->control_no."','".$inv->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

		        Log::info("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($inv->gatewayPayment->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->gatewayPayment->control_no."','".$inv->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

	        }else{
               $inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);

		        if($inv->gatewayPayment->psp_name == 'National Microfinance Bank'){
		            $bank_code = 619;
		            $bank_name = 'NMB';
		        }else{
		            $bank_code = 615;
		            $bank_name = 'CRDB';
		        }

		        $stud_reg = 'NULL';

		        $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','32342','20220212','Application','33243','".$stud_name."','13344324','1000442498','0','20221203')");

		       Log::info("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->gatewayPayment->transaction_id,5)."','".date('Ymd',strtotime($inv->gatewayPayment->datetime))."','".$inv->feeType->description."','".$stud_reg."','".$stud_name."','".$inv->gatewayPayment->control_no."','".$inv->gatewayPayment->paid_amount."','0','".date('Ymd',strtotime(now()))."')");
	        }
		}

		
    }

    /**
     * Get Reconciliation
     */
    public function getReconciliation(Request $request)
    {
    	$jsondata = file_get_contents('php://input');
		$response = json_decode($jsondata, true);
		$x=0;												
		foreach ($response['ReconcTrans']['ReconcTrxInf'] as $recon_data) {
                $SpReconcReqId = $response['ReconcBatchInfo']['SpReconcReqId'];
                $SpCode = $response['ReconcBatchInfo']['ReconcStsCode'];
                $SpBillId = $recon_data['SpBillId'];
                $BillCtrNum = $recon_data['BillCtrNum'];
                $pspTrxId = $recon_data['pspTrxId'];
                $PaidAmt = $recon_data['PaidAmt'];
                $CCy = $recon_data['CCy'];
                $PayRefId = $recon_data['PayRefId'];
                $TrxDtTm = $recon_data['TrxDtTm'];
                $CtrAccNum = $recon_data['CtrAccNum'];
                $UsdPayChnl= $recon_data['UsdPayChnl'];
                $PspName = $recon_data['PspName'];
                $PspCode = $recon_data['PspCode'];
                $DptCellNum = $recon_data['DptCellNum'];
                $DptName = $recon_data['DptName'];
				$DptEmailAddr = $data['DptEmailAddr']; 
                $Remarks = $recon_data['Remarks'];
                $ReconcRsv1 = $recon_data['ReconcRsv1'];
                $ReconcRsv2= $recon_data['ReconcRsv2'];
                $ReconcRsv3= $recon_data['ReconcRsv3'];
				// $ReconcRsv2=NULL;
    //             $ReconcRsv3=NULL;
				
				// $rquery=$db->runquery("replace into gepg_reconcile(SpReconcReqId,ReconcStsCode,SpBillId,BillCtrNum,pspTrxId,PaidAmt,CCy,PayRefId,TrxDtTm,
				// 	   CtrAccNum,UsdPayChnl,PspName,PspCode,DptCellNum,DptName,DptEmailAddr,Remarks,ReconcRsv1,ReconcRsv2,ReconcRsv3)
					   
				// 			VALUES('$SpReconcReqId','$SpCode', '$SpBillId','$BillCtrNum','$pspTrxId','$PaidAmt',
				// 					'$CCy','$PayRefId','$TrxDtTm', '$CtrAccNum', '$UsdPayChnl', '$PspName','$PspCode',
				// 					'$DptCellNum','$DptName','$DptEmailAddr','$Remarks','$ReconcRsv1','$ReconcRsv2','$ReconcRsv3')");

				$recon = new PaymentReconciliation;
				$recon->SpReconcReqId = $SpReconcReqId;
				//$recon->ReconcStsCode = $ReconcStsCode;
				$recon->SpBillId = $SpBillId;
				$recon->BillCtrNum = $BillCtrNum;
				$recon->pspTrxId = $pspTrxId;
				$recon->PaidAmt = $PaidAmt;
				$recon->CCy = $CCy;
				$recon->PayRefId = $PayRefId;
				$recon->TrxDtTm = $TrxDtTm;
				$recon->CtrAccNum = $CtrAccNum;
				$recon->UsdPayChnl = $UsdPayChnl;
				$recon->PspName = $PspName;
				$recon->PspCode = $PspCode;
				$recon->DptCellNum = $DptCellNum;
				$recon->DptName = $DptName;
				$recon->DptEmailAddr = $DptEmailAddr;
				$recon->Remarks = $Remarks;
				$recon->ReconcRsv1 = $ReconcRsv1;
				$recon->ReconcRsv2 = $ReconcRsv2;
				$recon->ReconcRsv3 = $ReconcRsv3;
				$recon->save();

           $x++;
        }
    }
}
