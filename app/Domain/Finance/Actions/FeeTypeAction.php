<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Repositories\Interfaces\FeeTypeInterface;

class FeeTypeAction implements FeeTypeInterface{
	
	public function store(Request $request){
		$type = new FeeType;
		$type->name = $request->get('name');
        $type->code = $request->get('code');
        $type->gfs_code = $request->get('gfs_code');
        $type->payment_option = $request->get('payment_option');
        $type->duration = $request->get('duration');
        $type->description = $request->get('description');
        $type->is_external = $request->get('payer') == 'EXTERNAL' || $request->get('payer') == 'BOTH'? 1 : 0;
        $type->is_internal = $request->get('payer') == 'INTERNAL' || $request->get('payer') == 'BOTH'? 1 : 0;
        $type->is_paid_per_semester = $request->get('when_paid') == 'PAID_PER_SEMESTER'? 1 : 0;
        $type->is_paid_only_once = $request->get('when_paid') == 'PAID_ONLY_ONCE'? 1 : 0;
        $type->save();
	}

	public function update(Request $request){
		$type = FeeType::find($request->get('fee_type_id'));
		$type->name = $request->get('name');
        $type->code = $request->get('code');
        $type->gfs_code = $request->get('gfs_code');
        $type->payment_option = $request->get('payment_option');
        $type->duration = $request->get('duration');
        $type->description = $request->get('description');
        $type->is_external = $request->get('payer') == 'EXTERNAL' || $request->get('payer') == 'BOTH'? 1 : 0;
        $type->is_internal = $request->get('payer') == 'INTERNAL' || $request->get('payer') == 'BOTH'? 1 : 0;
        $type->is_paid_per_semester = $request->get('when_paid') == 'PAID_PER_SEMESTER'? 1 : 0;
        $type->is_paid_only_once = $request->get('when_paid') == 'PAID_ONLY_ONCE'? 1 : 0;
        $type->save();
	}
}