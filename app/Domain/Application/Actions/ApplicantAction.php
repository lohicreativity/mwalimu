<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Repositories\Interfaces\ApplicantInterface;
use App\Utils\DateMaker;
use App\Utils\SystemLocation;

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
                $applicant->surname = $request->get('surname');
                $applicant->email = $request->get('email');
                $applicant->phone = $request->get('phone');
                $applicant->birth_date = DateMaker::toDBDate($request->get('birth_date'));
                $applicant->nationality = $request->get('nationality');
                $applicant->gender = $request->get('gender');
                $applicant->disability_status_id = $request->get('disability_status_id');
                $applicant->address = $request->get('address');
                $applicant->country_id = $request->get('country_id');
                $applicant->region_id = $request->get('region_id');
                $applicant->district_id = $request->get('district_id');
                $applicant->ward_id = $request->get('ward_id');
                $applicant->street = $request->get('street');
                if($request->hasFile('birth_certificate')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('birth_certificate')->move($destination, $request->file('birth_certificate')->getClientOriginalName());
                  // $file_name = SystemLocation::renameFile($destination, $request->file('image')->getClientOriginalName(), $request->file('image')->guessClientExtension());

                  $applicant->birth_certificate = $request->file('birth_certificate')->getClientOriginalName();
                }
                $applicant->save();
	}
}