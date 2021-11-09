<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Applicant;
use App\Domain\Academic\Repositories\Interfaces\ApplicantInterface;

class ApplicantAction implements ApplicantInterface{
	
	public function store(Request $request){
		$applicant = new Applicant;
        $applicant->first_name = $request->get('first_name');
        $applicant->middle_name = $request->get('middle_name');
        $applicant->last_name = $request->get('last_name');
        $applicant->birth_date = $request->get('birth_date');
        $applicant->nationality = $request->get('nationality');
        $applicant->gender = $request->get('gender');
        $applicant->save();
	}

	public function update(Request $request){
		$applicant = Applicant::find($request->get('applicant_id'));
        $applicant->first_name = $request->get('first_name');
        $applicant->middle_name = $request->get('middle_name');
        $applicant->last_name = $request->get('last_name');
        $applicant->birth_date = $request->get('birth_date');
        $applicant->nationality = $request->get('nationality');
        $applicant->gender = $request->get('gender');
        $applicant->save();
	}
}