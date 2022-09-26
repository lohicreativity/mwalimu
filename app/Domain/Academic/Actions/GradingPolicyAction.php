<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\GradingPolicy;
use App\Domain\Academic\Repositories\Interfaces\GradingPolicyInterface;

class GradingPolicyAction implements GradingPolicyInterface{
	
	public function store(Request $request){
		$policy = new GradingPolicy;
        $policy->nta_level_id = $request->get('nta_level_id');
        $policy->study_academic_year_id = $request->get('study_academic_year_id');
        $policy->min_score = $request->get('min_score');
        $policy->max_score = $request->get('max_score');
        $policy->grade = $request->get('grade');
        $policy->point = $request->get('point');
        $policy->remark = $request->get('remark');
        $policy->save();
	}

	public function update(Request $request){
		$policy = GradingPolicy::find($request->get('grading_policy_id'));
        $policy->nta_level_id = $request->get('nta_level_id');
        $policy->study_academic_year_id = $request->get('study_academic_year_id');
        $policy->min_score = $request->get('min_score');
        $policy->max_score = $request->get('max_score');
        $policy->grade = $request->get('grade');
        $policy->point = $request->get('point');
        $policy->remark = $request->get('remark');
        $policy->save();
	}
}