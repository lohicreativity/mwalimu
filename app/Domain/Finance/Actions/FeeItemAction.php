<?php

namespace App\Domain\Finance\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Repositories\Interfaces\FeeItemInterface;

class FeeItemAction implements FeeItemInterface{
	
	public function store(Request $request){
		$item = new FeeItem;
                $item->name = $request->get('name');
                $item->description = $request->get('description');
                $item->payment_order = $request->get('payment_order');
                $item->fee_type_id = $request->get('fee_type_id');
                $item->is_mandatory = $request->get('is_mandatory');
                $item->campus_id = $request->get('campus_id');
                $item->save();
	}

	public function update(Request $request){
		$item = Feeitem::find($request->get('fee_item_id'));
                $item->name = $request->get('name');
                $item->description = $request->get('description');
                $item->payment_order = $request->get('payment_order');
                $item->fee_type_id = $request->get('fee_type_id');
                $item->is_mandatory = $request->get('is_mandatory');
                $item->campus_id = $request->get('campus_id');
                $item->save();
	}
}