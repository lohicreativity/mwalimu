<?php

namespace App\Http\Controllers\Gepg;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;

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
		$control_no = $data['data']['control_no'];

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
    	$jsondata = file_get_contents('php://input');
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
		$payer_email = $data['payer_email'];
		$payer_name = $data['payer_name'];
		$psp_receipt_no = $data['psp_receipt_no'];

		$gatepay = new GatewayPayment;
		$gatepay->transaction_id = $transaction_id;
		$gatepay->sp_code = $sp_code;
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
		$gatepay->save();
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
				//$ReconcRsv2=NULL;
                //$ReconcRsv3=NULL;
				
				// $rquery=$db->runquery("replace into gepg_reconcile(SpReconcReqId,ReconcStsCode,SpBillId,BillCtrNum,pspTrxId,PaidAmt,CCy,PayRefId,TrxDtTm,
				// 	   CtrAccNum,UsdPayChnl,PspName,PspCode,DptCellNum,DptName,DptEmailAddr,Remarks,ReconcRsv1,ReconcRsv2,ReconcRsv3)
					   
				// 			VALUES('$SpReconcReqId','$SpCode', '$SpBillId','$BillCtrNum','$pspTrxId','$PaidAmt',
				// 					'$CCy','$PayRefId','$TrxDtTm', '$CtrAccNum', '$UsdPayChnl', '$PspName','$PspCode',
				// 					'$DptCellNum','$DptName','$DptEmailAddr','$Remarks','$ReconcRsv1','$ReconcRsv2','$ReconcRsv3')");

        $x++;
}
    }
}
