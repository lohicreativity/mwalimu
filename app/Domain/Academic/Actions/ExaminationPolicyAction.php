<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ExaminationPolicy;
use App\Domain\Academic\Repositories\Interfaces\ExaminationPolicyInterface;

class ExaminationPolicyAction implements ExaminationPolicyInterface{
	
	public function store(Request $request){
		$policy = new ExaminationPolicy;
        $policy->nta_level_id = $request->get('nta_level_id');
        $policy->study_academic_year_id = $request->get('study_academic_year_id');
        $policy->course_work_min_mark = $request->get('course_work_min_mark');
        $policy->course_work_percentage_pass = $request->get('course_work_percentage_pass');
        $policy->course_work_pass_score = $request->get('course_work_pass_score');
        $policy->final_min_mark = $request->get('final_min_mark');
        $policy->final_percentage_pass = $request->get('final_percentage_pass');
        $policy->final_pass_score = $request->get('final_pass_score');
        $policy->module_pass_mark = $request->get('module_pass_mark');
        $policy->type = $request->get('type');
        $policy->save();
	}

	public function update(Request $request){
		$policy = ExaminationPolicy::find($request->get('examination_policy_id'));
        $policy->nta_level_id = $request->get('nta_level_id');
        $policy->study_academic_year_id = $request->get('study_academic_year_id');
        $policy->course_work_min_mark = $request->get('course_work_min_mark');
        $policy->course_work_percentage_pass = $request->get('course_work_percentage_pass');
        $policy->course_work_pass_score = $request->get('course_work_pass_score');
        $policy->final_min_mark = $request->get('final_min_mark');
        $policy->final_percentage_pass = $request->get('final_percentage_pass');
        $policy->final_pass_score = $request->get('final_pass_score');
        $policy->module_pass_mark = $request->get('module_pass_mark');
        $policy->type = $request->get('type');
        $policy->save();
	}
}