<?php

namespace App\Http\Controllers\GePG;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use function \FluidXml\fluidxml;
use Validator, Amqp;

class BillController extends Controller
{
    public function store(Request $request)
    {    	
        //die( $request->get("payer_name") );
        //$this->validate($request, ['payment_ref' => 'required']);
    	$valid = $this->validateRequest($request);
    	if($valid->fails()){
            return $this->error($valid->errors()->first(),500);
        }

    	
    	#set expire da//
    	if($request->get('days_expires_after')) {
    		$days = $request->get('days_expires_after');	
    	}
    	else {
    		$days = config('contants.DEFAULT_EXPIRE_AFTER');	
    	}        
        $expire_date = Date('Y-m-d'.'\T'.'h:i:s', strtotime("+$days days"));

  // Log::info($expire_date);

        # Compose Bill XML
         $EquivAmount=$this->equivalentAmount($request);
         $amount=$this->validateAmount($request);

        $bill = fluidxml(false);         
        $bill->add('gepgBillSubReq', true)
                ->add('BillHdr', true)
                    ->add('SpCode', config('constants.SPCODE'))
                    ->add('RtrRespFlg', 'true')
                ->appendSibling('BillTrxInf',true)
                    ->add('BillId', $request->get("payment_ref"))
                    ->add('SubSpCode', config('constants.SUBSPCODE'))
                    //->add('SubSpCode', $request->get("sub_sp_code"))
                    ->add('SpSysId', config('constants.SPSYSID'))
                    ->add('BillAmt',$amount )
                    ->add('MiscAmt', '0.0')
                    ->add('BillExprDt', $expire_date)
                    ->add('PyrId', $request->get("payerid"))
                    ->add('PyrName',  htmlentities($request->get("payer_name")) )
                    ->add('BillDesc', $request->get("desc"))
                    ->add('BillGenDt', date('Y-m-d'.'\T'.'h:i:s'))
                    ->add('BillGenBy', $request->get("generated_by"))
                    ->add('BillApprBy', $request->get("approved_by"))
                    ->add('PyrCellNum', '255759623399')
                    ->add('PyrEmail', $request->get("payer_email"))
                    ->add('Ccy', $request->get("currency"))
                    ->add('BillEqvAmt', $EquivAmount)
                    ->add('RemFlag', 'false')
                    ->add('BillPayOpt', $request->get("payment_type"))
                    ->add('BillItems', true)
                        ->add('BillItem', true)
                            ->add('BillItemRef', $request->get("payment_ref"))
                            ->add('UseItemRefOnPay', 'N')
                            // ->add('BillItemAmt', number_format($request->get("amount")))
                            ->add('BillItemAmt',$amount)
                            ->add('BillItemEqvAmt', $EquivAmount )
                            ->add('BillItemMiscAmt', '0.0')
                            ->add('GfsCode', $request->get("gfs_code"));  


             //die( $request->get("payer_name") );              
       // print_r ($bill->xml(true));
       // die();

       //die();
       # Add Bill to Q                    
		Amqp::publish('gepg.bill.out', $bill->xml(true), ['exchange' => 'sp_exchange', 'queue' => 'bill.to.gepg']);
		return $this->success("The bill with id {$request->get('payment_ref')} has been queued.", 200);
    }


    public function postReconciliation(Request $request)
    {   

        // Log::info(print_r($request->all(), true)); die;    
        $valid = $this->validateReconRequest($request);
        if($valid->fails()){
            return $this->error($valid->errors()->first(),500);
        }        
            
        # Compose Reconciliation Request XML
        $reconcile_req = fluidxml(false);         
        $reconcile_req->add('gepgSpReconcReq', true)
                        ->add('SpReconcReqId', $request->get('trx_id'))
                        ->add('SpCode', config('constants.SPCODE'))
                        ->add('SpSysId', config('constants.SPSYSID'))
                        ->add('TnxDt', $request->get('trx_date'))
                        ->add('ReconcOpt', $request->get('recon_type')); 

    // $ack_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $reconcile_req->xml(true)));
    //     # Add Bill to Q                    
    //     \Amqp::publish('gepg.recon.out', $ack_body, ['exchange' => 'sp_exchange', 'queue' => 'recon.to.gepg']);
        
    //     return $this->success("The Reconciliation Request with Transaction ID {$request->get('trx_id')} has been queued.", 200);        
        Amqp::publish('gepg.recon.out', $reconcile_req->xml(true), ['exchange' => 'sp_exchange', 'queue' => 'recon.to.gepg']);
        return $this->success("The Reconciliation Request with TransactionX ID {$request->get('trx_id')} has been queued.", 200);          
    }

/*
    public function destroy($bill_id = null)
    {        
        //$valid = $this->validateRequest($request);
        if(empty($bill_id)) {
            return $this->error("Invalid Bill ID.", 500);
        }
            
        # Compose BillCancel XML
        $bill_cancel_req = fluidxml(false);         
        $bill_cancel_req->add('gepgBillCanclReq', true)
                ->add('SpCode', config('constants.SPCODE'))
                ->add('SpSysId', config('constants.SPSYSID'))
                ->add('BillId', $bill_id);  

       $url = config('constants.GePG_SERVER').config('constants.CANCEL_BILL_PATH');       
        Log::info('URL '.$url);

        #var_dump($message->body);
        $headers = [
            'Content-Type' => 'application/xml',
            'Gepg-Com' => 'default.sp.in',
            'Gepg-Code' => config('constants.SPCODE')
        ];
        $client = new Client();
        $request = new GuzzleReq(
                'POST',
                $url,
                $headers,
                $bill_cancel_req->xml()
        );      

        $client = new Client(['http_errors' => false]);
            
        Log::info('BODY '.$bill_cancel_req->xml());
        $response = $client->post($url,$headers,$bill_cancel_req->xml());
        


        # if success
        if($response->getStatusCode() == 200 ) {
            Log::info('CANCEL-RESP '.$response->getBody());
            $cancel_resp = fluidxml();
            $cancel_resp->add($response->getBody());
            
            $sxml = simplexml_load_string($cancel_resp->xml());
            $data = json_decode(json_encode($sxml), true);    

            $cancel_trx = $data['gepgBillCanclResp']['BillCanclTrxDt'];

            if($cancel_trx['TrxSts'] == 'GS') {
                $return = [
                    'message'   =>  'Successful',
                    'data'      =>  ['bill_id' => $cancel_trx['BillId']]
                ];                
                return $this->success($return, 200);            
            }
            else {
                $return = [
                    'message'   =>  $cancel_trx['TrxStsCode'],
                    'data'      =>  ['bill_id' => $cancel_trx['BillId']]
                ];    
                return $this->error($return, 200);            
            }            
        }    
        else {
            $return = [
                'message'   =>  $response->getStatusCode().": Server Error, Check Logs.",
                'data'      =>  []
            ];
            return $this->error($return, 200);            
        }               
    }

*/
    public function destroy($bill_id = null)
    {        
        //$valid = $this->validateRequest($request);
        if(is_null($bill_id)) {
            return $this->error("Invalid Bill ID.", 500);
        }
          

        //dd("BillID: ".$bill_id);
        # Compose BillCancel XML
        $bill_cancel_req = fluidxml(false);         
        $bill_cancel_req->add('gepgBillCanclReq', true)
                ->add('SpCode', config('constants.SPCODE'))
                ->add('SpSysId', config('constants.SPSYSID'))
                ->add('BillId', $bill_id);  

        $req_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $bill_cancel_req->xml(true)));
        
        Log::info("CANCEL-BODY: ".$req_body); 
        # Opening Certificate
        if (!$cert_store = file_get_contents("/home/public_html/mnmaa_new/consumers/gepgclientprivatekey.pfx")) {
            echo " ** Error: Unable to read the cert file", "\n";
            exit;
        }
        else
        {
            # Reading Certificate Info
            if (openssl_pkcs12_read($cert_store, $cert_info, config('constants.CERT_PASSWORD')))   
            {  
                # Create signature
                openssl_sign($req_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

                # output crypted data base64 encoded
                $signature = base64_encode($signature);                         

                # Combine signature and content signed
                $body = fluidxml(false);
                $body->add("Gepg", true)
                     ->add($req_body)
                     ->add("gepgSignature", $signature);
                     
                $body = str_replace('> <','><', preg_replace('/\s+/', ' ', $body->xml(true)));

                $url = config('constants.GePG_SERVER').config('constants.CANCEL_BILL_PATH');       
                Log::info('URL '.$url);
                Log::info("CANCEL-BODY: ".$body); 
                #var_dump($message->body);
                $headers = [
                    'Content-Type:application/xml',
                    'Gepg-Com:default.sp.in',
                    'Gepg-Code:'. config('constants.SPCODE')
                ];

                $response = curlRequest($url, null, $body, $headers);

                Log::info( $response['code']);
                Log::info( $response['body']. "\n");

                 if( $response['code'] == 200 ) {

                    # Get data and signature from response
                    $vdata = getDataString($response['body'], config('constants.CANCEL_DATA_TAG'));
                    $vsignature = getSignatureString($response['body'], config('constants.SIGN_TAG'));

                    # Get Certificate contents
                    if (!$pcert_store = file_get_contents("/home/public_html/mnmaa_new/consumers/gepgpubliccertificate.pfx")) {
                    Log::info(" ** Error: Unable to read the GePG Public Cert File\n");
                    //exit;
                    } else {

                        # Read Certificate
                       // if (openssl_pkcs12_read($pcert_store, $pcert_info, config('constants.PUBLIC_CERT_PASSWORD'))) {

                            # Decode Received Signature String
                           // $rawsignature = base64_decode($vsignature);

                            # Verify Signature and state whether signature is okay or not
                            $vdata = str_replace('> <', '><', preg_replace('/\s+/', ' ', $vdata));                
                           // $ok = openssl_verify($vdata, $rawsignature, $pcert_info['extracerts']['0']);

                           // if ($ok == 1) {
                                // echo "Signature Status:";
                                // echo "GOOD";

                                Log::info("\n * Cancel Response: ".json_encode($response['body']));  
                                $cancel_resp = fluidxml();
                                $cancel_resp->add($vdata);
                                
                                $sxml = simplexml_load_string($cancel_resp->xml());
                                $data = json_decode(json_encode($sxml), true);    

                                $cancel_trx = $data['gepgBillCanclResp']['BillCanclTrxDt'];

                                if($cancel_trx['TrxSts'] == 'GS') {
                                    $return = [
                                        'message'   =>  'Successful',
                                        'data'      =>  ['bill_id' => $cancel_trx['BillId']]
                                    ];                
                                    return $this->success($return, 200);            
                                }
                                else {
                                    $return = [
                                        'message'   =>  $cancel_trx['TrxStsCode'],
                                        'data'      =>  ['bill_id' => $cancel_trx['BillId']]
                                    ];    
                                    return $this->error($return, 200);            
                                } 
                            /*    
                            }

                            else 
                            {
                                return $this->error("Invalid Signature from GePG.", 500);
                            }
                        }//end opening public certificate
                       */

                    }
                }
                else
                {
                    $return = [
                        'message'   =>  $response['code'].": Server Error, Check Logs.",
                        'data'      =>  []
                    ];
                    return $this->error($return, 200);
                }

            }
        }                    
    }


    public function validateRequest(Request $request){
		 $rules = [
		 	'payment_ref' => 'required',
			'sub_sp_code' => 'required',
			'amount' => 'required', 
			'desc' => 'required',
			'gfs_code' => 'required|numeric', 
			'payment_type' => 'required|in:0,1,2,3',
			'payerid' => 'required', 
			'payer_name' => 'required',
			'payer_cell' => 'required', 
			'payer_email' => 'required|email',
			'days_expires_after' => 'required|numeric', 
			'generated_by' => 'required', 
			'approved_by' => 'required|alpha_dash',
            'currency' => 'required'
		 ];

		 //$this->validate($request, $rules);
		 return Validator::make($request->all(), $rules);		
	}


    public function equivalentAmount(Request $request){
      
          $EquivAmount=NULL;
          $currency=$request->get("currency");

          if($currency=="TZS"){
               $EquivAmount=$request->get("amount");
          }elseif($currency=="USD"){
            $factor=2307.94;
                $EquivAmount=$factor* doubleval($request->get("amount"));
          }elseif($currency=="POUND" || $currency=="GBP"){
             $factor=2846.61;
                 $EquivAmount=$factor* doubleval($request->get("amount"));
          }elseif($currency=="EURO"){
             $factor=2601.97;
               $EquivAmount=$factor*doubleval($request->get("amount"));
	  }else{
               $EquivAmount=$request->get("amount");
	  }

        return $EquivAmount;
    }

    public function validateAmount(Request $request){

        $amount=NULL;
        $amount=$request->get("amount");
        if($amount<=0){

            $amount1=NULL;

        }else{
            $amount1=$amount;
        }

        return $amount1;

    }

    public function validateReconRequest(Request $request){
         $rules = [
            'trx_id' => 'required',
            'trx_date' => 'required',
            'recon_type' => 'required|in:1,2,3'            
         ];

         return Validator::make($request->all(), $rules);       
    }
}
