<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\EntryRequirement;
use App\Domain\Application\Repositories\Interfaces\EntryRequirementInterface;
use App\Utils\Util;

class EntryRequirementAction implements EntryRequirementInterface{
	
	public function store(Request $request){
		
        $group_id = Util::randString(100);
        foreach($request->get('campus_program_ids') as $id){
            $requirement = new EntryRequirement;
            $requirement->campus_program_id = $id;
            $requirement->application_window_id = $request->get('application_window_id');
            $requirement->equivalent_gpa = $request->get('equivalent_gpa');
            $requirement->equivalent_pass_subjects = $request->get('equivalent_pass_subjects');
            $requirement->equivalent_average_grade = $request->get('equivalent_average_grade');
            $requirement->open_equivalent_gpa = $request->get('open_equivalent_gpa');
            $requirement->open_equivalent_pass_subjects = $request->get('open_equivalent_pass_subjects');
            $requirement->open_equivalent_average_grade = $request->get('open_equivalent_average_grade');
            $requirement->principle_pass_points = $request->get('principle_pass_points');
            $requirement->min_principle_pass_points = $request->get('min_principle_pass_points');
            $requirement->principle_pass_subjects = $request->get('principle_pass_subjects');
            $requirement->subsidiary_pass_subjects = $request->get('subsidiary_pass_subjects');
            $requirement->pass_subjects = $request->get('pass_subjects');
            $requirement->pass_grade = $request->get('pass_grade');
            $requirement->award_level = $request->get('award_level');
            $requirement->nta_level = $request->get('nta_level');
            $requirement->exclude_subjects = serialize($request->get('exclude_subjects'));
            $requirement->must_subjects = serialize($request->get('must_subjects'));
            $requirement->other_must_subjects = serialize($request->get('other_must_subjects'));
            $requirement->other_advance_must_subjects = serialize($request->get('other_advance_must_subjects'));
            $requirement->advance_exclude_subjects = serialize($request->get('advance_exclude_subjects'));
            $requirement->advance_must_subjects = serialize($request->get('advance_must_subjects'));
            $requirement->subsidiary_subjects = serialize($request->get('subsidiary_subjects'));
            $requirement->principle_subjects = serialize($request->get('principle_subjects'));
            $requirement->max_capacity = $request->get('max_capacity');
            $requirement->group_id = $group_id;
            $requirement->save();
        }
        
	}

	public function update(Request $request)
      {
            $req = EntryRequirement::find($request->get('entry_requirement_id'));

            $reqs = EntryRequirement::where('group_id',$req->group_id)->get();
            foreach($reqs as $rq){
		$requirement = EntryRequirement::find($rq->id);
            $requirement->application_window_id = $request->get('application_window_id');
            $requirement->equivalent_gpa = $request->get('equivalent_gpa');
            $requirement->equivalent_pass_subjects = $request->get('equivalent_pass_subjects');
            $requirement->equivalent_average_grade = $request->get('equivalent_average_grade');
            $requirement->open_equivalent_gpa = $request->get('open_equivalent_gpa');
            $requirement->open_equivalent_pass_subjects = $request->get('open_equivalent_pass_subjects');
            $requirement->open_equivalent_average_grade = $request->get('open_equivalent_average_grade');
            $requirement->principle_pass_points = $request->get('principle_pass_points');
            $requirement->min_principle_pass_points = $request->get('min_principle_pass_points');
            $requirement->principle_pass_subjects = $request->get('principle_pass_subjects');
            $requirement->subsidiary_pass_subjects = $request->get('subsidiary_pass_subjects');
            $requirement->pass_subjects = $request->get('pass_subjects');
            $requirement->pass_grade = $request->get('pass_grade');
            $requirement->award_level = $request->get('award_level');
            $requirement->nta_level = $request->get('nta_level');
            $requirement->exclude_subjects = serialize($request->get('exclude_subjects'));
            $requirement->must_subjects = serialize($request->get('must_subjects'));
            $requirement->other_must_subjects = serialize($request->get('other_must_subjects'));
            $requirement->other_advance_must_subjects = serialize($request->get('other_advance_must_subjects'));
            $requirement->advance_exclude_subjects = serialize($request->get('advance_exclude_subjects'));
            $requirement->advance_must_subjects = serialize($request->get('advance_must_subjects'));
            $requirement->subsidiary_subjects = serialize($request->get('subsidiary_subjects'));
            $requirement->principle_subjects = serialize($request->get('principle_subjects'));
            $requirement->max_capacity = $request->get('max_capacity');
            $requirement->save();
            }
	}
}