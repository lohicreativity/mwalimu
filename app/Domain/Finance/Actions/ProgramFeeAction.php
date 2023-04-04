<?php

namespace App\Domain\Finance\Actions;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Repositories\Interfaces\ProgramFeeInterface;

class ProgramFeeAction implements ProgramFeeInterface{
	
	public function store(Request $request){
        foreach($request->get('campus_program_id') as $id){
            if(ProgramFee::where('campus_program_id',$id)
				->where('study_academic_year_id',$request->get('study_academic_year_id'))
				->where('year_of_study', $request->get('year_of_study'))->count() != 0){
                 return redirect()->back()->with('error','Programme fee already exists');
            }		
			$fee = new ProgramFee;
                $fee->campus_program_id = $request->get('campus_program_id');
                $fee->amount_in_tzs = $request->get('amount_in_tzs');
                $fee->amount_in_usd = $request->get('amount_in_usd');
                $fee->fee_item_id = $request->get('fee_item_id');
                $fee->year_of_study = $request->get('year_of_study');
                $fee->study_academic_year_id = $request->get('study_academic_year_id');
                $fee->save();
		}
	}

	public function update(Request $request){
		$fee = ProgramFee::find($request->get('program_fee_id'));
                $fee->campus_program_id = $request->get('campus_program_id');
                $fee->amount_in_tzs = $request->get('amount_in_tzs');
                $fee->amount_in_usd = $request->get('amount_in_usd');
                $fee->fee_item_id = $request->get('fee_item_id');
                $fee->year_of_study = $request->get('year_of_study');
                $fee->study_academic_year_id = $request->get('study_academic_year_id');
                $fee->save();
	}
}