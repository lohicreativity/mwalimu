<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ProgramModuleAssignment;
use App\Domain\Academic\Repositories\Interfaces\ProgramModuleAssignmentInterface;

class ProgramModuleAssignmentAction implements ProgramModuleAssignmentInterface{
	
	public function store(Request $request){
		$assignment = new ProgramModuleAssignment;
                $assignment->semester_id = $request->get('semester_id');
                $assignment->campus_program_id = $request->get('campus_program_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->year_of_study = $request->get('year_of_study');
                $assignment->category = $request->get('category');
                $assignment->save();
	}

	public function update(Request $request){
	        $assignment = ProgramModuleAssignment::find($request->get('program_module_assignment_id'));
                $assignment->semester_id = $request->get('semester_id');
                $assignment->campus_program_id = $request->get('campus_program_id');
                $assignment->study_academic_year_id = $request->get('study_academic_year_id');
                $assignment->module_id = $request->get('module_id');
                $assignment->year_of_study = $request->get('year_of_study');
                $assignment->category = $request->get('category');
                $assignment->save();
	}
}