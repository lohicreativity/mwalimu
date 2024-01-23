<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Department;
use App\Domain\Academic\Repositories\Interfaces\DepartmentInterface;
use Auth, DB;

class DepartmentAction implements DepartmentInterface{
	
	public function store(Request $request){
		$department = new Department;
        $department->name = $request->get('name');
        $department->description = $request->get('description');
        $department->abbreviation = $request->get('abbreviation');
        $department->unit_category_id = $request->get('unit_category_id');
        $department->parent_id = $request->get('parent_id');
        $department->save();

/*         if (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {
            $department->campuses()->sync($request->get('campus_id'));
        } else if (Auth::user()->hasRole('admission-officer')) { */
            $department->campuses()->sync($request->get('campus_id'));
        //}

	}

	public function update(Request $request){
		$department = Department::find($request->get('department_id'));
        $department->name = $request->get('name');
        $department->description = $request->get('description');
        $department->abbreviation = $request->get('abbreviation');
        $department->unit_category_id = $request->get('unit_category_id');
        $department->parent_id = $request->get('parent_id');
        $department->save();
        //$department->campuses()->sync($request->get('campus_id'));

        DB::table('campus_department')->where('department_id',$request->get('department_id'))
                                      ->where('campus_id',$request->get('campus_id'))
                                      ->update(['campus_id'=>$request->get('campus_id')]);
//$program->departments()->attach([$request->get('department_id')=>['campus_id'=>$request->get('campus_id')]]);


/*         if (Auth::user()->hasRole('administrator')) {
            $department->campuses()->sync($request->get('campuses'));

        } else if (Auth::user()->hasRole('admission-officer')) {
            $department->campuses()->sync($request->get('staff_campus'));
        } */

	}
}