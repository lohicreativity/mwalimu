<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ModuleAssignmentRequest;
use App\Domain\Academic\Repositories\Interfaces\ModuleAssignmentRequestInterface;
use Auth;

class ModuleAssignmentRequestAction implements ModuleAssignmentRequestInterface{
	
	public function store(Request $request){
		$assignment = new ModuleAssignmentRequest;
                $assignment->department_id = $request->get('department_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->campus_program_id = $request->get('campus_program_id');
                $assignment->program_module_assignment_id = $request->get('program_module_assignment_id');
                $assignment->requested_by_user_id = Auth::user()->id;
                $assignment->save();
	}

}