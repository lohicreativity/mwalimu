<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentReconciliationController extends Controller
{
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

	    $ack_body = str_replace('> <','><', preg_replace('/\s+/', ' ', $reconcile_req->xml(true)));
	        # Add Bill to Q                    
	        \Amqp::publish('gepg.recon.out', $ack_body, ['exchange' => 'sp_exchange', 'queue' => 'recon.to.gepg']);
	        
	        return $this->success("The Reconciliation Request with Transaction ID {$request->get('trx_id')} has been queued.", 200);                
	    }
}
