<?php

namespace App\Http\Controllers\GePG;

use Illuminate\Http\Request;
use Validator, Config, Amqp, Log;
use function \FluidXml\fluidxml;

class GePGController extends Controller
{
    public function getBill()
    {
      Log::info('wemetuma bill');
       $arrContextOptions=array(
            "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
         ),
        );  
        //$jsondata = file_get_contents('php://input');
        $bill_resp=file_get_contents('php://input', false, stream_context_create($arrContextOptions));
        
        # get the raw POST data
        //$bill_resp = file_get_contents("php://input");
        
        if ( is_null($bill_resp) ) {            
            return $this->error("Invalid Bill Response Provided.");
        }

        //Log::info(print_r($bill_resp,true));

        # Get data and signature from response
        $vdata = getDataString($bill_resp, config('constants.CN_DATA_TAG'));
        $vsignature = getSignatureString($bill_resp, config('constants.SIGN_TAG'));
        
        # Get Certificate contents
        if (!$pcert_store = file_get_contents("consumers/gepgpubliccertificate.pfx")) {
            //echo " ** Error: Unable to read the GePG Public Cert File\n";
            //exit;

        Log::info('imepitia if');
        } 
        else 
        {
        
            # Read Certificate
           // if (openssl_pkcs12_read($pcert_store, $pcert_info, config('constants.PUBLIC_CERT_PASSWORD'))) {

                    # Decode Received Signature String
                //$rawsignature = base64_decode($vsignature);

                    # Verify Signature and state whether signature is okay or not
                $vdata = str_replace('> <', '><', preg_replace('/\s+/', ' ', $vdata));              
               // $ok = openssl_verify($vdata, $rawsignature, $pcert_info['extracerts']['0']);

               // if ($ok == 1) {
                        //echo "Signature Status:";
                        //echo "GOOD";

                        // Log::info('GEPG-BILL-RESP '.$bill_resp);
                    $resp = fluidxml();
                    $resp->add($vdata);

                    $sxml = simplexml_load_string($resp->xml());
                    $data = json_decode(json_encode($sxml), true);                                       

                    $bill_trx = $data['gepgBillSubResp']['BillTrxInf'];
  Log::info('-----Billing-------',$bill_trx);
                        # if success
                    if( $bill_trx['TrxSts'] == 'GS' ) {
                        $return = [
                            'status'    =>  1,
                            'message'   =>  'Successful',
                            'data'      =>  ['bill_id' => $bill_trx['BillId'], 'control_no' =>$bill_trx['PayCntrNum']]
                        ];
                    }    
                    else {
                        $return = [
                            'status'    =>  0,
                            'message'   =>  $bill_trx['TrxStsCode'],
                            'data'      =>  ['bill_id' => $bill_trx['BillId'], 'control_no' =>$bill_trx['PayCntrNum']]
                        ];
                    }        
                        # Q Bill Response for sending to SP                    
                    \Amqp::publish('gepg.bill.in', json_encode($return), ['exchange' => 'sp_exchange', 'queue' => 'bill.res.to.sp']);

                    return $this->billRespAck();
                 /*   

                }
                else 
                {

                    echo "Signature Status: BAD!";
                    echo "Nnaitema hii.";

                }
                */

             /*   
            }
            else 
            {
               Log::info('Signature mbaya');
               echo " ** Error: Unable to read the Gateway Public cert store.\n";

           }
           */
       }    	

       // return $this->error("Invalid Bill Response Provided.");
   }


   public function getReceipt()
   {
    	# get the raw POST data
       $payment_receipt = file_get_contents("php://input");

        Log::info('-----START RECEIPT-------');

        Log::info(print_r($payment_receipt,true));

        Log::info('----End RECEIPT------------');
      
       if ( is_null($payment_receipt) ) {
        throw new Exception("Invalid Payment Receipt Provided.");
    }

        //Log::info(print_r($payment_receipt,true));

        # Get data and signature from response
        $vdata = getDataString($payment_receipt, config('constants.RECPT_DATA_TAG'));
        $vsignature = getSignatureString($payment_receipt, config('constants.SIGN_TAG'));

        # Get Certificate contents
    if (!$pcert_store = file_get_contents("consumers/gepgpubliccertificate.pfx")) {
      	 Log::info(" ** Error: Unable to read the GePG Public Cert File\n");
	 throw new Exception(" ** Error: Unable to read the GePG Public Cert File\n");            
    } 
    else 
    {
	Log::info("NIMEWEZA KUSOMA GePG Cert File");
            # Read Certificate
        //if (openssl_pkcs12_read($pcert_store, $pcert_info, config('constants.PUBLIC_CERT_PASSWORD'))) {                
                    # Decode Received Signature String
           // $rawsignature = base64_decode($vsignature);

                    # Verify Signature and state whether signature is okay or not
            $vdata = str_replace('> <', '><', preg_replace('/>\s+\</', '> <', $vdata));                
           // $ok = openssl_verify($vdata, $rawsignature, $pcert_info['extracerts']['0']);

            //if ($ok == 1) {
                        Log::info("Signature Status: GOOD") ;
                        //echo "GOOD";

                $resp = fluidxml();
                $resp->add($vdata);

                $sxml = simplexml_load_string($resp->xml());                                    
                $data = json_decode(json_encode($sxml), true);                  
                
                $pay_trx = $data['gepgPmtSpInfo']['PymtTrxInf'];                
                Log::info($pay_trx['PayCtrNum'].":   TUNAIPELEKA KWA QQQQ");
                $receipt = [
                    'transaction_id'    =>  $pay_trx['TrxId'],
                    'sp_code' =>  $pay_trx['SpCode'],
                    'pay_ref_id' =>  $pay_trx['PayRefId'],
                    'control_no' =>  $pay_trx['PayCtrNum'],
                    'bill_id'    =>  $pay_trx['BillId'],
                    'bill_amount' =>  $pay_trx['BillAmt'],
                    'paid_amount'    =>  $pay_trx['PaidAmt'],
                    'pay_option'    =>  $pay_trx['BillPayOpt'],
                    'currency' =>  $pay_trx['CCy'],
                    'datetime' =>  $pay_trx['TrxDtTm'],
                    'payment_channel' =>  $pay_trx['UsdPayChnl'],
                    'cell_number' =>  $pay_trx['PyrCellNum'],
                    'payer_name' =>  $pay_trx['PyrName'],
                    'payer_email' =>  $pay_trx['PyrEmail'],
                    'psp_name' =>  $pay_trx['PspName'],
                    'credited_acc_num' =>  $pay_trx['CtrAccNum'],
                    'psp_receipt_no' =>  $pay_trx['PspReceiptNumber']
                ];    

                        # Q Receipt for sending to SP                
                \Amqp::publish('gepg.receipt', json_encode($receipt), ['exchange' => 'sp_exchange', 'queue' => 'receipt.to.sp']);		

                return $this->pmtSpInfoAck();
             /*   
            }
	    else {
		Log::info($ok.": SIGNATURE HAIKO POA, NAITEMA HII...");
	    }
        */
       /*
        }// end reading certificate
        else { 
	    Log::info(" ** Error: Unable to read the GePG Public Cert File\n");               
            throw new Exception(" ** Error: Unable to read the GePG Public Cert File\n");
        } 
      */
    }

        //return $this->error("Invalid Receipt Provided.");
        //throw new Exception("Invalid Receipt Provided.");
    // return "Invalid";
}


public function getReconciliation()
{
ini_set('memory_limit', '-1');
    	# get the raw POST data
   $reconciliation_data = file_get_contents("php://input");        

   if ( is_null($reconciliation_data) ) {
    throw new Exception("Invalid reconciliation data provided.");
}

        # Get data and signature from response
$vdata = getDataString($reconciliation_data, config('constants.RECON_DATA_TAG'));
$vsignature = getSignatureString($reconciliation_data, config('constants.SIGN_TAG'));

        # Get Certificate contents
if (!$pcert_store = file_get_contents("consumers/gepgpubliccertificate.pfx")) {
    throw new Exception(" ** Error: Unable to read the GePG Public Cert File\n");            
} 
else 
{
            # Read Certificate
   // if (openssl_pkcs12_read($pcert_store, $pcert_info, config('constants.PUBLIC_CERT_PASSWORD'))) {                
                    # Decode Received Signature String
       // $rawsignature = base64_decode($vsignature);

                    # Verify Signature and state whether signature is okay or not
        $vdata = str_replace('> <', '><', preg_replace('/\s+/', ' ', $vdata));                
        //$ok = openssl_verify($vdata, $rawsignature, $pcert_info['extracerts']['0']);

       // if ($ok == 1) {
                        // echo "Signature Status:";
                        // echo "GOOD";

            $resp = fluidxml();
            $resp->add($vdata);

            $sxml = simplexml_load_string($resp->xml());
            $data = json_decode(json_encode($sxml), true);
                        //$data = $data['Gepg'];
                        // Log::info(print_r($data,true));           

            //$recon_data = $data['gepgSpReconcResp']; 
              $recon_data1 = $data['gepgSpReconcResp'];     
 Log::info("Middleware Recon GEPG:",
            [
                "data" => $recon_data1,
               
            ]);
        
                Log::info($recon_data1['ReconcBatchInfo']['SpReconcReqId'].":   TUNAIPELEKA KWA QQQQ");
         $data_rec= ['data'=> $recon_data1] ;  
        Log::info("Recon GEPG:",
        [
            "data" =>  json_encode($recon_data1),

        ]); 



                       # Q Reconciliation for sending to SP            
         \Amqp::publish('gepg.recon', json_encode($recon_data1), ['exchange' => 'sp_exchange', 'queue' => 'recon.to.sp']);		
return $this->gepgSpReconcRespAck();
           
        /*    
        }
        else
        {
            throw new Exception(" ** Error: Invalid Signature Provided\n");                                    
        }
      */
    //}// end reading signature



}
                  
}


    /**
     * Method to prepare GePG Bill Submission Response Acknowledgement
     *
     */
    public function billRespAck()
    {
        $body = null;
        $ack = fluidxml(false);
        $ack->add('gepgBillSubRespAck', true)
        ->add('TrxStsCode', 7101);
        
        $ack_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $ack->xml(true)));
        //$ack_body = "<gepgBillSubRespAck><TrxStsCode>7101</TrxStsCode></gepgBillSubRespAck>";
        Log::info("ACK-BODY: ".$ack_body); 
	# Opening Certificate
        if (!$cert_store = file_get_contents("consumers/gepgclientprivatekey.pfx")) {
            echo " ** Error: Unable to read the cert file", "\n";
            exit;
        }
        else
        {
            # Reading Certificate Info
            if (openssl_pkcs12_read($cert_store, $cert_info, config('constants.CERT_PASSWORD')))   
            {  
                # Create signature
                openssl_sign($ack_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

                # output crypted data base64 encoded
                $signature = base64_encode($signature);         
                //echo " ** Signature of Signed Ack"."\n".$signature."\n";

                # Combine signature and content signed
                $body = fluidxml(false);
                $body->add("Gepg", true)
                ->add($ack_body)
                ->add("gepgSignature", $signature);

                $body = str_replace('> <','><', preg_replace('/\s+/', ' ', $body->xml(true)));
            }
            else
            {
                echo " ** Error: Unable to read the cert2 file", "\n";
                exit;
            }
        }
        Log::info(print_r('Print out'.$body,true));
        // echo '<Gepg><gepgBillSubRespAck><TrxStsCode>7101</TrxStsCode></gepgBillSubRespAck><gepgSignature>'.$signature.'</gepgSignature></Gepg>';
        return $body;
        
    }


    /**
     * Method to prepare GePG Reconciliation Response Acknowledgement
     *
     */
    public function gepgSpReconcRespAck()
    {
 Log::info('Recon Ack Start');
 
        $body = null;
        $ack = fluidxml(false);
        $ack->add('gepgSpReconcRespAck', true)
        ->add('ReconcStsCode', 7101);
        
        $ack_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $ack->xml(true)));
        # Opening Certificate
        if (!$cert_store = file_get_contents("consumers/gepgclientprivatekey.pfx")) {
            echo " ** Error: Unable to read the cert file", "\n";
            exit;
        }
        else
        {
            # Reading Certificate Info
            if (openssl_pkcs12_read($cert_store, $cert_info, config('constants.CERT_PASSWORD')))   
            {  
                # Create signature
                openssl_sign($ack_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

                # output crypted data base64 encoded
                $signature = base64_encode($signature);         
                //echo " ** Signature of Signed Ack"."\n".$signature."\n";

                # Combine signature and content signed
                $body = fluidxml(false);
                $body->add("Gepg", true)
                ->add($ack_body)
                ->add("gepgSignature", $signature);

                $body = str_replace('> <','><', preg_replace('/\s+/', ' ', $body->xml(true)));
            }
            else
            {
                echo " ** Error: Unable to read the cert file", "\n";
                exit;
            }
        }
Log::info(print_r('Print out'.$body,true));

        return $body;  //format this into a straight line with no space between tags
        // return '<Gepg><gepgBillSubRespAck><TrxStsCode>7101</TrxStsCode></gepgBillSubRespAck><gepgSignature>'.$signature.'</gepgSignature></Gepg>';
    }


    /**
     * Method to prepare GePG Payment Information posting Acknowledgement
     *
     */
    
    public function pmtSpInfoAck()
    {
        $body = null;
        $ack = fluidxml(false);
        $ack->add('gepgPmtSpInfoAck', true)
        ->add('TrxStsCode', 7101);
        
        $ack_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $ack->xml(true)));
        //$ack_body='<gepgPmtSpInfoAck><TrxStsCode>7101</TrxStsCode></gepgPmtSpInfoAck>';
        //$ack_body=$ack->xml(true);
        # Opening Certificate
        if (!$cert_store = file_get_contents("consumers/gepgclientprivatekey.pfx")) {
            echo " ** Error: Unable to read the cert file", "\n";
            exit;
        }
        else
        {
            # Reading Certificate Info
            if (openssl_pkcs12_read($cert_store, $cert_info, config('constants.CERT_PASSWORD')))   
            {  
                # Create signature
                openssl_sign($ack_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

                # output crypted data base64 encoded
                $signature = base64_encode($signature);         
                //echo " ** Signature of Signed Ack"."\n".$signature."\n";

                # Combine signature and content signed
                $body = fluidxml(false);
                $body->add("Gepg", true)
                ->add($ack_body)
                ->add("gepgSignature", $signature);
                $body = str_replace('> <','><', preg_replace('/\s+/', ' ', $body->xml(true)));
                //echo $body;
            }
            else
            {
                echo " ** Error: Unable to read the cert file", "\n";
                exit;
            }
        }
        Log::info(print_r('Malipo ya risiti hapa'.$body,true));
         return $body;  //format this into a straight line with no space between tags
        // return '<Gepg><gepgBillSubRespAck><TrxStsCode>7101</TrxStsCode></gepgBillSubRespAck><gepgSignature>'.$signature.'</gepgSignature></Gepg>';
     }
    // start test

     /*
    public function pmtSpInfoAck()
    {
        //var $pass='ifm2019';
              
        $ack_body = "<gepgPmtSpInfoAck><TrxStsCode>7101</TrxStsCode></gepgPmtSpInfoAck>";
        # Opening Certificate
        if (!$cert_store = file_get_contents("consumers/gepgclientprivatekey.pfx")) {
            echo " ** Error: Unable to read the cert file", "\n";
            exit;
        }
        else
        {
            # Reading Certificate Info
            if (openssl_pkcs12_read($cert_store, $cert_info,"ifm2019"))   
            {  
                # Create signature
                openssl_sign($ack_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

                # output crypted data base64 encoded
                $signature = base64_encode($signature);         
               

                # Combine signature and content signed
                $body = "<Gepg>".$ack_body."<gepgSignature>".$signature."</gepgSignature></Gepg>";
               
            }
            else
            {
                echo " ** Error: Unable to read the cert file", "\n";
                exit;
            }
        }
      
         return $body;  
     }
    */


     //test
}
