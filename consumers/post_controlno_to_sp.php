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
$channel->queue_declare('bill.res.to.sp', false, true, false, false);
$channel->queue_declare('bill.to.sp.retry', false, true, false, false, false, [
	'x-dead-letter-exchange' => ['S','sp_exchange'],
    	'x-dead-letter-routing-key' => ['S','gepg.bill.in'],
	'x-message-ttl' => ['I', RETRY_INTERVAL], 
    	'durable' => ['S','true']
]);

$channel->queue_bind('bill.res.to.sp', 'sp_exchange', 'gepg.bill.in');
$channel->queue_bind('bill.to.sp.retry', 'sp_exchange', 'sp.bill.cntrlno'); 

 echo ' * Waiting for messages. To exit press CTRL+C', "\n";
 $cb_bill_out = function($msg) {	
     
	echo " ***** *** ****** *** **** **** *** ***** *** ***** ***** ***** ****** ******* ****** *****", "\n";
	echo " * Message received", "\n";
	echo $msg->body. "\n";
    
	$response = curlRequest(SP_SERVER.SP_BILL_PATH, null, $msg->body);

	echo $response['code'];	
	echo $response['body']. "\n";

	if( $response['code'] == 200 ) {
    	    	
		echo "\n * CN => SP Response: ".json_encode($response['body']), "\n";
		$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);    
	}
    # If not successfully posted, Retry
    else 
    {
	    echo "\n * Message failed to be posted to SP. Retrying ...", "\n";    
		// if( $retry > 0 ){
		// $data['retry']--;
		// $msg->body = json_encode($msg->body);
    	$msg->delivery_info['channel']->basic_publish($msg,'sp_exchange','sp.bill.cntrlno');
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
$channel->basic_consume('bill.res.to.sp', '', false, false, false, false, $cb_bill_out);

while(count($channel->callbacks)) {
    $channel->wait();
}
