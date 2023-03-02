<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Repositories\Interfaces\DepartmentInterface;
use Auth;

class DepartmentAction implements DepartmentInterface{
	
	public function store(Request $request){
		$department = new Department;
        $department->name = $request->get('name');
        $department->description = $request->get('description');
        $department->abbreviation = $request->get('abbreviation');
        $department->unit_category_id = $request->get('unit_category_id');
        $department->parent_id = $request->get('parent_id');
        $department->save();

        if (Auth::user()->hasRole('administrator')) {
            $department->campuses()->sync($request->get('campuses'));
        } else if (Auth::user()->hasRole('admission-officer')) {
            $department->campuses()->sync($request->get('staff_campus'));
        }

	}

	public function update(Request $request){
		$department = Department::find($request->get('department_id'));
        $department->name = $request->get('name');
        $department->description = $request->get('description');
        $department->abbreviation = $request->get('abbreviation');
        $department->unit_category_id = $request->get('unit_category_id');
        $department->parent_id = $request->get('parent_id');
        $department->save();

        if (Auth::user()->hasRole('administrator')) {
            $department->campuses()->sync($request->get('campuses'));

        } else if (Auth::user()->hasRole('admission-officer')) {
            $department->campuses()->sync($request->get('staff_campus'));
        }

	}
}