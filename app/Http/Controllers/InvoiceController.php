<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator, Config, Amqp;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\FeeType;
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

        $generated_by = 'SP';
        $approved_by = 'SP';
        $inst_id = Config::get('constants.SPCODE');

        return $this->requestControlNumber($request,
        	                        $invoice->reference_no,
        	                        $inst_id,
        	                        $invoice->amount,
        	                        $fee_type->description,
        	                        $fee_type->gfs_code,
        	                        $fee_type->payment_option,
        	                        $payable->id,
        	                        $payable->first_name.' '.$payable->middle_name.' '.$payable->surname,
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
				'currency'=>$currency,
				'_token'=>session()->token()
 			);

			//$txt=print_r($data, true);
			//$myfile = file_put_contents('/var/public_html/ifm/logs/req_bill.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
            $url = url('bills/post_bill');
			$result = Http::post($url,$data);

			return $result;

			
		return redirect()->back()->with('message','The bill with id '.$billno.' has been queued.', 200);
						
		}


		
}
