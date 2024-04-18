<?php
require_once 'config.php';
require_once 'helpers.php';
require_once 'vendor/autoload.php';
require_once 'producer.php';
use PhpAmqpLib\Connection\AMQPConnection;
use function \FluidXml\fluidxml;
/*
$connection = new AMQPConnection(RABBIT_HOST,RABBIT_PORT,RABBIT_USER,RABBIT_PASS);            
$channel = $connection->channel();
$channel->exchange_declare('wcf_exchange', 'direct', false, true, false);
$channel->queue_declare('recon.to.gepg', false, true, false, false);
$channel->queue_declare('recon.to.gepg.retry', false, true, false, false, false, [
		'x-dead-letter-exchange' => ['S','wcf_exchange'],
    	'x-dead-letter-routing-key' => ['S','gepg.recon.out'],
		'x-message-ttl' => ['I', RETRY_INTERVAL], 
    	'durable' => ['S','true']
]);

$channel->queue_bind('recon.to.gepg', 'wcf_exchange', 'gepg.recon.out');
$channel->queue_bind('recon.to.gepg.retry', 'wcf_exchange', 'gepg.recon.out.retry'); 
*/

$connection = new AMQPConnection(RABBIT_HOST,RABBIT_PORT,RABBIT_USER,RABBIT_PASS);            
$channel = $connection->channel();
$channel->exchange_declare('sp_exchange', 'direct', false, true, false);
$channel->queue_declare('recon.to.gepg', false, true, false, false);
$channel->queue_declare('recon.to.gepg.retry', false, true, false, false, false, [
		'x-dead-letter-exchange' => ['S','sp_exchange'],
    	'x-dead-letter-routing-key' => ['S','gepg.recon.out'],
		'x-message-ttl' => ['I', RETRY_INTERVAL], 
    	'durable' => ['S','true']
]);

$channel->queue_bind('recon.to.gepg', 'sp_exchange', 'gepg.recon.out');
$channel->queue_bind('recon.to.gepg.retry', 'sp_exchange', 'gepg.recon.out.retry'); 


 echo ' * Waiting for messages. To exit press CTRL+C', "\n";
 $cb_bill_out = function($msg) {	
     
	echo " ***** *** ****** *** **** **** *** ***** *** ***** ***** ***** ****** ******* ****** *****", "\n";
    echo " * Message received", "\n";
    
	$recon_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $msg->body));
	echo $recon_body. "\n";
	echo "Reconciliation Req Length: ".strlen($recon_body)."\n\n";

    # Opening Certificate
	if (!$cert_store = file_get_contents("/home/mnmaadmin/public_html/mwalimu/consumers/gepgclientprivatekey.pfx")) {
	    echo " ** Error: Unable to read the cert file", "\n";
	    exit;
	}
	else
	{
        # Reading Certificate Info
		if (openssl_pkcs12_read($cert_store, $cert_info, CERT_PASSWORD))   
		{
            #echo " ** Certificate Information", "\n";
		    #print_r($cert_info['pkey']);

		    # Create signature
			openssl_sign($recon_body, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");

			# output crypted data base64 encoded
		    $signature = base64_encode($signature);         
		    echo " ** Signature of Signed Content"."\n".$signature."\n";

            # Combine signature and content signed
            $body = fluidxml(false);
		    $body->add("Gepg", true)
		    	 ->add($recon_body)
	    		 ->add("gepgSignature", $signature);
	     			
        	$data = str_replace('> <', '><', preg_replace('/\s+/', ' ', $body->xml(true)));                
            echo "Recon Req To POST \n";
            echo $data."\n\n";

            $headers = [
                'content-type: application/xml',
                'gepg-com: default.sp.in',
                'gepg-code: '.SPCODE
            ];
            $response = curlRequest(SERVER.SIGNED_RECONCILE_PATH, null, $data, $headers);
        
            echo $response['code'];	
            echo $response['body']. "\n";
        
            echo "RESPONSE: ".json_encode($response). "\n";
            if( $response['code'] == 200 ) 
            {
                # Get data and signature from response
				$vdata = getDataString($response['body'], RECON_DATA_TAG);
				$vsignature = getSignatureString($response['body'], SIGN_TAG);
				
				echo "\n";
				echo "Data Received:\n";
				echo $vdata;
				echo "\n";
				echo "Data Length:\n";
				echo strlen($vdata);
				echo "\n";
				echo "Signature Received:\n";
				echo $vsignature;
				echo "\n";

				# Get Certificate contents
				if (!$pcert_store = file_get_contents("/home/mnmaadmin/public_html/mwalimu/consumers/gepgpubliccertificate.pfx")) {
	    			echo " ** Error: Unable to read the GePG Public Cert File\n";
	    			//exit;
                } 
                else 
                {
                    # Read Certificate
					//if (openssl_pkcs12_read($pcert_store, $pcert_info, PUBLIC_CERT_PASSWORD)) {
                        
                            # Decode Received Signature String
                           // $rawsignature = base64_decode($vsignature);
    
                            # Verify Signature and state whether signature is okay or not
                            $vdata = str_replace('> <', '><', preg_replace('/\s+/', ' ', $vdata));                
                           // $ok = openssl_verify($vdata, $rawsignature, $pcert_info['extracerts']['0']);

                           // if ($ok == 1) {
                                echo "Signature Status:";
                                echo "GOOD";

                                echo "\n * Post Reconciliation Response: ".json_encode($response['body']), "\n";  
                                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
                            /*
                            }
                            else 
                            {
                                echo "Signature Status:";
                                echo "BAD";
    
                                goto retry;
                            }
                            */

                    /*        
                    } //end reading certificate
                    else 
                    {
                        echo " ** Error: Unable to read the Gateway Public cert store.\n";
                        goto retry;
                    }
                    */
                  
                }
            }   
            # If not successfully posted, Retry
		    else 
		    {
		    	retry:
			    echo "\n * Message failed to be posted. Retrying ...", "\n";    
				// if( $retry > 0 ){
				// $data['retry']--;
				// $msg->body = json_encode($msg->body);
		    	$msg->delivery_info['channel']->basic_publish($msg,'wcf_exchange','gepg.recon.out.retry');
				// } else {
				// 	#echo "\n * Message discarded after ".RETRY_COUNT." Failed Attempts", "\n";
				// 	echo "\n * After ".RETRY_COUNT." Failed Attempts, I'll make another ".RETRY_COUNT." Attempts", "\n";
				// 	$data['retry'] = RETRY_COUNT;
				// 	$msg->body = json_encode($data);
				//     $msg->delivery_info['channel']->basic_publish($msg,'wcf_exchange','gepg.bill.out.retry');
				// }
				$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
			} 	    	
            

        }
        else 
        {
            echo " ** Error: Unable to read the cert store.\n";
			goto retry;
        }

    }	  
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('recon.to.gepg', '', false, false, false, false, $cb_bill_out);

while(count($channel->callbacks)) {
    $channel->wait();
}
