<?php

namespace App\Http\Controllers\GePG;

use Illuminate\Http\Request;

class ConsumerController extends Controller
{
    public function postBill()
    {
    	\Amqp::consume('bill.to.gepg', function ($message, $resolver) {
		$url = config('constants.GePG_SERVER').config('constants.POST_BILL_PATH');		
		//Log::info('URL '.$url);
		$headers = [
			'content-type: application/xml',
			'gepg-com: default.sp.in',
			'gepg-code: ' .config('constants.SPCODE')
		];
		
		//$client = new Client(['http_errors' => false]);
		
		Log::info('HEADERS '.json_encode($headers));	
		Log::info('BODY '.$message->body);
			
		$resp = curlRequest($url, null, $message->body, $headers);
		Log::info('BILL-RESPONSE '.$resp['code'].': '.$resp['body']);
			
	  	# check if http status not 200, then add to retry Q
	  	if($resp['code'] != 200 ) {
	    	\Amqp::publish('gepg.bill.out.retry', $message->body, [
		        'queue_properties' => [
		            "x-ha-policy" => ["S", "all"],
		            'x-message-ttl' => ['I', config('constants.RETRY_INTERVAL')],
		            'x-dead-letter-exchange' => ['S', 'sp_exchange'],
		            'x-dead-letter-routing-key' => ['S', 'gepg.bill.out'],
		        ],
		        'queue' => 'bill.to.gepg.retry'
		    ]);
	    }			
								
	    $resolver->acknowledge($message);
	    $resolver->stopWhenProcessed();
		        
		});
    }

    public function postReconciliation()
    {
    	\Amqp::consume('recon.to.gepg', function ($message, $resolver) {
		$url = config('constants.GePG_SERVER').config('constants.RECONCILE_PATH');		
		Log::info('URL '.$url);

		$headers = [
			'content-type: application/xml',
			'gepg-com: default.sp.in',
			'gepg-code: ' .config('constants.SPCODE')
		];
		Log::info('HEADERS '.json_encode($headers));	
		Log::info('BODY '.$message->body);
		
	 	$resp = curlRequest($url, null, $message->body, $headers);
	  	Log::info('BILL-RESPONSE '.$resp['code'].': '.$resp['body']);
		
		# check if http status not 200, then add to retry Q
	    if($resp['code'] != 200 ) {
	    	\Amqp::publish('recon.out.retry', $message->body, [
		        'queue_properties' => [
		            "x-ha-policy" => ["S", "all"],
		            'x-message-ttl' => ['I', config('constants.RETRY_INTERVAL')],
		            'x-dead-letter-exchange' => ['S', 'sp_exchange'],
		            'x-dead-letter-routing-key' => ['S', 'gepg.recon.out'],
		        ],
		        'queue' => 'recon.to.gepg.retry'
		    ]);
	    }
			
	    $resolver->acknowledge($message);
	    $resolver->stopWhenProcessed();
		        
		});
    }

    public function postControlNo()
    {
    	\Amqp::consume('bill.res.to.sp', function ($message, $resolver) {
		$url = config('constants.SP_SERVER').config('constants.SP_BILL_PATH');		
		Log::info('URL '.$url);

		$headers = [
			'content-type: application/xml',
			'gepg-com: default.sp.in',
			'gepg-code: ' .config('constants.SPCODE')
		];
		Log::info('HEADERS '.json_encode($headers));	
		Log::info('BODY '.$message->body);
		
	 	$resp = curlRequest($url, null, $message->body, $headers);
	  	Log::info('BILL-RESPONSE '.$resp['code'].': '.$resp['body']);
		
	    # check if http status not 200, then add to retry Q
	    if($resp['code'] != 200 ) {
	    	\Amqp::publish('sp.bill.cntrlno', $message->body, [
		        'queue_properties' => [
		            "x-ha-policy" => ["S", "all"],
		            'x-message-ttl' => ['I', config('constants.RETRY_INTERVAL')],
		            'x-dead-letter-exchange' => ['S', 'sp_exchange'],
		            'x-dead-letter-routing-key' => ['S', 'gepg.bill.in'],
		        ],
		        'queue' => 'bill.to.sp.retry'
		    ]);
	    }	
							

	    $resolver->acknowledge($message);
	    $resolver->stopWhenProcessed();
		        
		});
    }


    public function postReceipt()
    {
    	\Amqp::consume('receipt.to.sp', function ($message, $resolver) {
		$url = config('constants.SP_SERVER').config('constants.SP_RECEIPT_PATH');		
		Log::info('URL '.$url);
		
		Log::info('BODY '.$message->body);
		
	 	$resp = curlRequest($url, null, $message->body);
	  	Log::info('BILL-RESPONSE '.$resp['code'].': '.$resp['body']);
		
		# check if http status not 200, then add to retry Q
	    if($resp['code'] != 200 ) {
	    	\Amqp::publish('sp.bill.cntrlno', $message->body, [
		        'queue_properties' => [
		            "x-ha-policy" => ["S", "all"],
		            'x-message-ttl' => ['I', config('constants.RETRY_INTERVAL')],
		            'x-dead-letter-exchange' => ['S', 'sp_exchange'],
		            'x-dead-letter-routing-key' => ['S', 'gepg.receipt'],
		        ],
		        'queue' => 'receipt.to.sp.retry'
		    ]);
	    }
			
	    $resolver->acknowledge($message);
	    //$resolver->stopWhenProcessed();
		        
		});
    }


    public function postRecon()
    {
    	\Amqp::consume('recon.to.sp', function ($message, $resolver) {
		$url = config('constants.SP_SERVER').config('constants.SP_RECON_PATH');		
		Log::info('URL '.$url);

		$headers = [
			'Content-Type' => 'application/json'			
		];
		Log::info('BODY '.$message->body);
		
	 	$resp = curlRequest($url, null, $message->body, $headers);
	  	Log::info('BILL-RESPONSE '.$resp['code'].': '.$resp['body']);
				
	    # check if http status not 200, then add to retry Q
	    if($resp['code'] != 200) {
	    	\Amqp::publish('sp.recon', $message->body, [
		        'queue_properties' => [
		            "x-ha-policy" => ["S", "all"],
		            'x-message-ttl' => ['I', config('constants.RETRY_INTERVAL')],
		            'x-dead-letter-exchange' => ['S', 'sp_exchange'],
		            'x-dead-letter-routing-key' => ['S', 'gepg.recon'],
		        ],
		        'queue' => 'recon.to.sp.retry'
		    ]);
	    }						
		
	    $resolver->acknowledge($message);
	    //$resolver->stopWhenProcessed();
		        
		});
    }
}
