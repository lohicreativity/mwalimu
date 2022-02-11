<?php
require_once 'config.php';
require_once 'helpers.php';
require_once 'vendor/autoload.php';
require_once 'producer.php';
use PhpAmqpLib\Connection\AMQPConnection;
use function \FluidXml\fluidxml;

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
	echo $msg->body. "\n";

    # Incase the message was for posting a bill to GePG     
	$headers = [
		'content-type: application/xml',
		'gepg-com: default.sp.in',
		'gepg-code: '.SPCODE
	];
	$response = curlRequest(SERVER.RECONCILE_PATH, null, $msg->body, $headers);

	echo $response['code'];	
	echo $response['body']. "\n";

	echo "RESPONSE: ".json_encode($response). "\n";
	if( $response['code'] == 200 ) {    	    	
		echo "\n * Post Reconciliation Response: ".json_encode($response['body']), "\n";  
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);  
	}
    # If not successfully posted, Retry
    else 
    {
	    echo "\n * Message failed to be posted. Retrying ...", "\n";    
		// if( $retry > 0 ){
		// $data['retry']--;
		// $msg->body = json_encode($msg->body);
    	$msg->delivery_info['channel']->basic_publish($msg,'sp_exchange','gepg.recon.out.retry');
		// } else {
		// 	#echo "\n * Message discarded after ".RETRY_COUNT." Failed Attempts", "\n";
		// 	echo "\n * After ".RETRY_COUNT." Failed Attempts, I'll make another ".RETRY_COUNT." Attempts", "\n";
		// 	$data['retry'] = RETRY_COUNT;
		// 	$msg->body = json_encode($data);
		//     $msg->delivery_info['channel']->basic_publish($msg,'sp_exchange','gepg.bill.out.retry');
		// }
		$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag']);
	}
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('recon.to.gepg', '', false, false, false, false, $cb_bill_out);

while(count($channel->callbacks)) {
    $channel->wait();
}
