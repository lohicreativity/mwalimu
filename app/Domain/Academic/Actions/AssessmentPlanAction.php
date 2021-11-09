<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\plan;
use App\Domain\Academic\Repositories\Interfaces\AssessmentPlanlanInterface;

class AssessmentPlanAction implements AssessmentPlanInterface{
	
	public function store(Request $request){
		$plan = new AssessmentPlan;
        $plan->staff_id = $request->get('staff_id');
        $plan->academic_year_id = $request->get('academic_year_id');
        $plan->module_id = $request->get('module_id');
        $plan->marks = $request->get('marks');
        $plan->weight = $request->get('weight');
        $plan->save();
	}

	public function update(Request $request){
		$plan = AssessmentPlan::find($request->get('assessment_plan_id'));
        $plan->staff_id = $request->get('staff_id');
        $plan->academic_year_id = $request->get('academic_year_id');
        $plan->module_id = $request->get('module_id');
        $plan->marks = $request->get('marks');
        $plan->weight = $request->get('weight');
        $plan->save();
	}
}