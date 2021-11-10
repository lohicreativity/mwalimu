<?php

namespace App\Domain\Registration\Actions;

use Illuminate\Http\Request;
use App\Domain\Registration\Models\Student;
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
                $table->entry_mode = $request->get('entry_mode');
                $table->type = $request->get('type');
                $table->phone = $request->get('phone');
                $table->email = $request->get('email');
                $table->address = $request->get('address');
                $table->registration_number = $request->get('registration_number');
                $table->registration_year = $request->get('registration_year');
                $table->year_of_study = $request->get('year_of_study');
                $table->study_academic_year_id = $request->get('study_academic_year_id');
                $table->studentship_status_id = $request->get('studentship_status_id');
                $table->program_id = $request->get('program_id');
                $table->applicant_id = $request->get('applicant_id');
                $table->disability_status = $request->get('disability_status');
                $student->save();
	}

	public function update(Request $request){
		$student = Student::find($request->get('student_id'));
                $student->first_name = $request->get('first_name');
                $student->middle_name = $request->get('middle_name');
                $student->last_name = $request->get('surname');
                $student->birth_date = $request->get('birth_date');
                $student->gender = $request->get('gender');
                $student->nationality = $request->get('nationality');
                $table->entry_mode = $request->get('entry_mode');
                $table->type = $request->get('type');
                $table->phone = $request->get('phone');
                $table->email = $request->get('email');
                $table->address = $request->get('address');
                $table->registration_number = $request->get('registration_number');
                $table->registration_year = $request->get('registration_year');
                $table->year_of_study = $request->get('year_of_study');
                $table->study_academic_year_id = $request->get('study_academic_year_id');
                $table->studentship_status_id = $request->get('studentship_status_id');
                $table->program_id = $request->get('program_id');
                $table->applicant_id = $request->get('applicant_id');
                $table->disability_status = $request->get('disability_status');
                $student->save();
	}
}