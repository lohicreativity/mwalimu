<?php

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Program;

class ProgramAction implements ProgramInterface{
	
	public function store(Request $request){
		$program = new Program;
        $program->name = $request->get('name');
        $program->code = $request->get('code');
        $program->department_id = $request->get('department_id');
        $program->save();
	}

	public function update(Request $request){
		$program = Program::find($request->get('program_id'));
        $program->name = $request->get('name');
        $program->code = $request->get('code');
        $program->department_id = $request->get('department_id');
        $program->save();
	}
}