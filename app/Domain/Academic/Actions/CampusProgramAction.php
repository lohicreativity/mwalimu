<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CampusProgram;
use App\Domain\Academic\Repositories\Interfaces\CampusProgramInterface;

class CampusProgramAction implements CampusProgramInterface{
	
	public function store(Request $request){
		$program = new CampusProgram;
                $program->program_id = $request->get('program_id');
                $program->campus_id = $request->get('campus_id');
                $program->regulator_code = $request->get('regulator_code');
                $program->save();
	}

	public function update(Request $request){
		$program = CampusProgram::find($request->get('campus_program_id'));
                $program->campus_id = $request->get('campus_id');
                $program->regulator_code = $request->get('regulator_code');
                $program->save();
	}
}