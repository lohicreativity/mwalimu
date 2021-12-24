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
                $assignment->type = $request->get('type');
                $assignment->course_work_min_mark = $request->get('course_work_min_mark');
                $assignment->course_work_percentage_pass = $request->get('course_work_percentage_pass');
                $assignment->course_work_pass_score = $request->get('course_work_pass_score');
                $assignment->final_min_mark = $request->get('final_min_mark');
                $assignment->final_percentage_pass = $request->get('final_percentage_pass');
                $assignment->final_pass_score = $request->get('final_pass_score');
                $assignment->module_pass_mark = $request->get('module_pass_mark');
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
                $assignment->type = $request->get('type');
                $assignment->course_work_min_mark = $request->get('course_work_min_mark');
                $assignment->course_work_percentage_pass = $request->get('course_work_percentage_pass');
                $assignment->course_work_pass_score = $request->get('course_work_pass_score');
                $assignment->final_min_mark = $request->get('final_min_mark');
                $assignment->final_percentage_pass = $request->get('final_percentage_pass');
                $assignment->final_pass_score = $request->get('final_pass_score');
                $assignment->module_pass_mark = $request->get('module_pass_mark');
                $assignment->save();
	}
}