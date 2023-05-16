<?php

namespace App\Domain\Finance\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Repositories\Interfaces\FeeAmountInterface;

class FeeAmountAction implements FeeAmountInterface{
	
	public function store(Request $request){
		$amount = new FeeAmount;
                $amount->amount_in_tzs = $request->get('amount_in_tzs');
                $amount->amount_in_usd = $request->get('amount_in_usd');
                $amount->fee_item_id = $request->get('fee_item_id');
                $amount->campus_id = $request->get('campus_id');
                $amount->study_academic_year_id = $request->get('study_academic_year_id');
                $amount->save();
	}

	public function update(Request $request){
		$amount = FeeAmount::find($request->get('fee_amount_id'));
                $amount->amount_in_tzs = $request->get('amount_in_tzs');
                $amount->amount_in_usd = $request->get('amount_in_usd');
                $amount->fee_item_id = $request->get('fee_item_id');
                $amount->campus_id = $request->get('campus_id');
                $amount->study_academic_year_id = $request->get('study_academic_year_id');
                $amount->save();
	}
}