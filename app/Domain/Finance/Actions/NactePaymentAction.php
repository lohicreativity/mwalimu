<?php

namespace App\Domain\Finance\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\NactePayment;
use App\Domain\Finance\Repositories\Interfaces\NactePaymentInterface;

class NactePaymentAction implements NactePaymentInterface{
	
	public function store(Request $request){
		$payment = new NactePayment;
                $payment->study_academic_year_id = $request->get('study_academic_year_id');
                $payment->campus_id = $request->get('campus_id');
                $payment->reference_no = $request->get('reference_number');
                $payment->amount = $request->get('amount');
                $payment->save();
	}

	public function update(Request $request){
		$payment = NactePayment::find($request->get('nacte_payment_id'));
                $payment->study_academic_year_id = $request->get('study_academic_year_id');
                $payment->campus_id = $request->get('campus_id');
                $payment->reference_no = $request->get('reference_number');
                $payment->amount = $request->get('amount');
                $payment->save();
	}
}