<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Repositories\Interfaces\ProgramInterface;

class ProgramAction implements ProgramInterface{
	
	public function store(Request $request){
		$program = new Program;
                $program->name = $request->get('name');
                $program->code = $request->get('code');
                $program->department_id = $request->get('department_id');
                $program->nta_level_id = $request->get('nta_level_id');
                $program->award_id = $request->get('award_id');
                $program->description = $request->get('description');
                $program->min_duration = $request->get('min_duration');
                $program->max_duration = $request->get('max_duration');
                // $program->category = $request->get('category');
                $program->save();
	}

	public function update(Request $request){
		$program = Program::find($request->get('program_id'));
                $program->name = $request->get('name');
                $program->code = $request->get('code');
                $program->department_id = $request->get('department_id');
                $program->nta_level_id = $request->get('nta_level_id');
                $program->award_id = $request->get('award_id');
                $program->description = $request->get('description');
                $program->min_duration = $request->get('min_duration');
                $program->max_duration = $request->get('max_duration');
                // $program->category = $request->get('category');
                $program->save();
	}
}