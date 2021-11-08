<?php

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;

class ExaminationAction implements ExaminationInterface{
	
	public function store(Request $request){
		$examination = new Department;
        $examination->name = $request->get('name');
        $examination->save();
	}

	public function update(Request $request){
		$examination = new Department::find($request->get('department_id'));
        $examination->name = $request->get('name');
        $examination->save();
	}
}