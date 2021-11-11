<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AssessmentPlan;
use App\Domain\Academic\Repositories\Interfaces\AssessmentPlanInterface;

class AssessmentPlanAction implements AssessmentPlanInterface{
	
	public function store(Request $request){
		$plan = new AssessmentPlan;
                $plan->name = $request->get('name');
                $plan->marks = $request->get('marks');
                $plan->module_assignment_id = $request->get('module_assignment_id');
                $plan->save();
	}

	public function update(Request $request){
		$plan = AssessmentPlan::find($request->get('assessment_plan_id'));
                $plan->name = $request->get('name');
                $plan->marks = $request->get('marks');
                $plan->module_assignment_id = $request->get('module_assignment_id');
                $plan->save();
	}
}