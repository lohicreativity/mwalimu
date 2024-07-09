<?php

namespace App\Domain\Registration\Actions;

use Illuminate\Http\Request;
use App\Domain\Registration\Models\Student;
use App\Domain\Application\Models\Applicant;
use App\Domain\Registration\Repositories\Interfaces\StudentInterface;

class StudentAction implements studentInterface{
	
	public function store(Request $request){
		$student = new Student;
                $student->first_name = $request->get('first_name');
                $student->middle_name = $request->get('middle_name');
                $student->last_name = $request->get('surname');
                $student->birth_date = $request->get('birth_date');
                $student->gender = $request->get('gender');
                $student->nationality = $request->get('nationality');
                $student->entry_mode = $request->get('entry_mode');
                $student->type = $request->get('type');
                $student->phone = $request->get('phone');
                $student->email = $request->get('email');
                $student->address = $request->get('address');
                $student->registration_number = $request->get('registration_number');
                $student->registration_year = $request->get('registration_year');
                $student->year_of_study = $request->get('year_of_study');
                $student->study_academic_year_id = $request->get('study_academic_year_id');
                $student->studentship_status_id = $request->get('studentship_status_id');
                $student->program_id = $request->get('program_id');
                $student->applicant_id = $request->get('applicant_id');
                $student->disability_status = $request->get('disability_status');
                $student->save();
	}

	public function update(Request $request){
		$student = Student::find($request->get('student_id'));
                $student->first_name = $request->get('first_name');
                $student->surname = $request->get('surname');
                $student->gender = $request->get('gender');
                $student->phone = $request->get('phone');
                $student->email = $request->get('email');
                $student->registration_number = $request->get('registration_number');
                $student->studentship_status_id = $request->get('studentship_status_id');
                $student->academic_status_id = $request->get('academic_status_id');				
                $student->campus_program_id = $request->get('campus_program_id');
                $student->applicant_id = $request->get('applicant_id');
                $student->disability_status_id = $request->get('disability_status_id');
                $student->study_mode = $request->get('study_mode');
                $student->user_id = $request->get('user_id');				
                $student->save();
				
 		$applicant = Applicant::find($request->get('applicant_id'));
				$applicant->address = $request->get('address');
/* 				$applicant->country_id = $request->get('country_id');
				$applicant->region_id = $request->get('region_id');
				$applicant->ward_id = $request->get('ward_id'); */
				$applicant->save(); 
	}
}