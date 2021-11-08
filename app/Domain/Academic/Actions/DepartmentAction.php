<?php

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;

class DepartmentAction implements DepartmentInterface{
	
	public function store(Request $request){
		$department = new Department;
        $department->name = $request->get('name');
        $department->save();
	}

	public function update(Request $request){
		$department = new Department::find($request->get('department_id'));
        $department->name = $request->get('name');
        $department->save();
	}
}