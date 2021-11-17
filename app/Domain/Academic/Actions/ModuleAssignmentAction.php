<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignment;
use App\Domain\Academic\Repositories\Interfaces\ModuleAssignmentInterface;

class ModuleAssignmentAction implements ModuleAssignmentInterface{
	
	public function store(Request $request){
		$assignment = new ModuleAssignment;
                $assignment->staff_id = $request->get('staff_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->program_module_assignment_id = $request->get('program_module_assignment_id');
                $assignment->save();
	}

	public function update(Request $request){
	        $assignment = ModuleAssignment::find($request->get('module_assignment_id'));
                $assignment->staff_id = $request->get('staff_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->program_module_assignment_id = $request->get('program_module_assignment_id');
                $assignment->save();
	}
}