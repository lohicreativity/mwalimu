<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ElectivePolicy;
use App\Domain\Academic\Repositories\Interfaces\ElectivePolicyInterface;

class ElectivePolicyAction implements ElectivePolicyInterface{
	
	public function store(Request $request){
		$policy = new ElectivePolicy;
                $policy->campus_program_id = $request->get('campus_program_id');
                $policy->study_academic_year_id = $request->get('study_academic_year_id');
                $policy->semester_id = $request->get('semester_id');
                $policy->number_of_options = $request->get('number_of_options');
                $policy->year_of_study = $request->get('year_of_study');
                $policy->save();
	}

	public function update(Request $request){
		$policy = ElectivePolicy::find($request->get('elective_policy_id'));
                $policy->campus_program_id = $request->get('campus_program_id');
                $policy->study_academic_year_id = $request->get('study_academic_year_id');
                $policy->semester_id = $request->get('semester_id');
                $policy->number_of_options = $request->get('number_of_options');
                $policy->year_of_study = $request->get('year_of_study');
                $policy->save();
	}
}