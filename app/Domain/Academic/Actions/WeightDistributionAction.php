<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\WeightDistribution;
use App\Domain\Academic\Repositories\Interfaces\WeightDistributionInterface;

class WeightDistributionAction implements WeightDistributionInterface{
	
	public function store(Request $request){
		$distribution = new WeightDistribution;
        $distribution->name = $request->get('name');
		$distribution->marks = $request->get('marks');
        $distribution->module_id = $request->get('module_id');
        $distribution->save();
	}

	public function update(Request $request){
		$distribution = WeightDistribution::find($request->get('distribution_id'));
		$distribution->name = $request->get('name');
		$distribution->marks = $request->get('marks');
        $distribution->module_id = $request->get('module_id');
        $distribution->save();
	}
}