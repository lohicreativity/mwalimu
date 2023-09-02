<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator, Config, Amqp;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Academic\Models\TranscriptRequest;
use App\Domain\Finance\Models\PaymentReconciliation;
use Illuminate\Support\Facades\Http;
use function \FluidXml\fluidxml;

class InvoiceController extends Controller
{

	/**
	 * Store invoice 
	 */
	public function store(Request $request)
	{
        $validation = Validator::make($request->all(),[
            'amount'=>'required|numeric'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }

        $invoice = new Invoice;
        $invoice->reference_no = 'MNMA-'.time();
        $invoice->amount = $request->get('amount');
        $invoice->currency = 'TZS';
        $invoice->payable_id = $request->get('payable_id');
        $invoice->payable_type = $request->get('payable_type');
        $invoice->fee_type_id = $request->get('fee_type_id');
        $invoice->save();


        $payable = Invoice::find($invoice->id)->payable;
        $fee_type = FeeType::find($request->get('fee_type_id'));

        if(str_contains($fee_type->name,'Transcript')){
            $transcript_req = new TranscriptRequest;
            $transcript_req->student_id = $request->payable_id;
            $transcript_req->payment_status = 'PENDING';
            $transcript_req->save();
        }

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = Config::get('constants.SUBSPCODE');

        $first_name = str_contains($payable->first_name,"'")? str_replace("'","",$payable->first_name) : $payable->first_name; 
        $surname = str_contains($payable->surname,"'")? str_replace("'","",$payable->surname) : $payable->surname;

        return $this->requestControlNumber($request,
        	                        $invoice->reference_no,
        	                        $inst_id,
        	                        $invoice->amount,
        	                        $fee_type->description,
        	                        $fee_type->gfs_code,
        	                        $fee_type->payment_option,
        	                        $payable->id,
        	                        $first_name.' '.$surname,
        	                        $payable->phone,
        	                        $payable->email,
        	                        $generated_by,
        	                        $approved_by,
        	                        $fee_type->duration,
        	                        $invoice->currency);
	}
    
    /**
     * Request control number
     */
    public function requestControlNumber(Request $request,$billno,$inst_id,$amount,$description,$gfs_code,$payment_option,$payerid,$payer_name,$payer_cell,$payer_email,$generated_by,$approved_by,$days,$currency){
			$data = array(
				'payment_ref'=>$billno,
				'sub_sp_code'=>$inst_id,
				'amount'=> $amount,
				'desc'=> $description,
				'gfs_code'=> $gfs_code,
				'payment_type'=> $payment_option,
				'payerid'=> $payerid,
				'payer_name'=> $payer_name,
				'payer_cell'=> $payer_cell,
				'payer_email'=> $payer_email,
				'days_expires_after'=> $days,
				'generated_by'=>$generated_by,
				'approved_by'=>$approved_by,
				'currency'=>$currency
 			);

			//$txt=print_r($data, true);
			//$myfile = file_put_contents('/var/public_html/ifm/logs/req_bill.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            $url = url('bills/post_bill');
			$result = Http::withHeaders([
                        'X-CSRF-TOKEN'=> csrf_token()
			          ])->post($url,$data);

			
		return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
						
		}

        public function showReconcile(Request $request)
        {
            return view('dashboard.finance.post-reconciliation')->withTitle('Post Reconciliation');
        }

        public function postReconcile(Request $request)
        {
                // $rqueryRecon1=$db->runquery("select max(SpReconcReqId)+1 id from gepg_reconcile");
    
                //  while($resRecon1=$db->fetch($rqueryRecon1)){
                //      if( $resRecon1["id"]==NULL || $resRecon1["id"]==""){
                //          $id=2;
                //      }else{
                //           $id=$resRecon1["id"];
                //      }
                //  }

                 $reconcile_id = PaymentReconciliation::max('id');
                 //new id
                 $trx_id=$reconcile_id? ($reconcile_id + 1) : 2;
                 //new date
                 $date = date('Y-m-d', time());
                 $trx_date = date('Y-m-d', strtotime($date .' -5 day'));
                 $recon_type=1;
                 
                 $data = array(
                            'trx_id'=>$trx_id,
                            'trx_date'=>$trx_date,
                            'recon_type'=>$recon_type
                 );

                 $url = url('bills/reconcile');
                 $result = Http::withHeaders([
                        'X-CSRF-TOKEN'=> csrf_token()
                      ])->post($url,$data);

                 return $result;
        }

}
