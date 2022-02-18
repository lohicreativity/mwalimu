<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NextOfKin;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Repositories\Interfaces\NextOfKinInterface;

class NextOfKinAction implements NextOfKinInterface{
	
	public function store(Request $request){
		$next_of_kin = new NextOfKin;
                $next_of_kin->first_name = $request->get('first_name');
                $next_of_kin->middle_name = $request->get('middle_name');
                $next_of_kin->surname = $request->get('surname');
                $next_of_kin->email = $request->get('email');
                $next_of_kin->phone = $request->get('phone');
                $next_of_kin->nationality = $request->get('nationality');
                $next_of_kin->relationship = $request->get('relationship');
                $next_of_kin->gender = $request->get('gender');
                $next_of_kin->address = $request->get('address');
                $next_of_kin->country_id = $request->get('country_id');
                $next_of_kin->region_id = $request->get('region_id');
                $next_of_kin->district_id = $request->get('district_id');
                $next_of_kin->ward_id = $request->get('ward_id');
                $next_of_kin->street = $request->get('street');
                $next_of_kin->save();

                $applicant = Applicant::find($request->get('applicant_id'));
                $applicant->next_of_kin_id = $next_of_kin->id;
                $applicant->save();
	}

	public function update(Request $request){
		$next_of_kin = NextOfKin::find($request->get('applicant_id'));
                $next_of_kin->first_name = $request->get('first_name');
                $next_of_kin->middle_name = $request->get('middle_name');
                $next_of_kin->surname = $request->get('surname');
                $next_of_kin->email = $request->get('email');
                $next_of_kin->phone = $request->get('phone');
                $next_of_kin->nationality = $request->get('nationality');
                $next_of_kin->relationship = $request->get('relationship');
                $next_of_kin->gender = $request->get('gender');
                $next_of_kin->address = $request->get('address');
                $next_of_kin->country_id = $request->get('country_id');
                $next_of_kin->region_id = $request->get('region_id');
                $next_of_kin->district_id = $request->get('district_id');
                $next_of_kin->ward_id = $request->get('ward_id');
                $next_of_kin->street = $request->get('street');
                $next_of_kin->save();

                $applicant = Applicant::find($request->get('applicant_id'));
                $applicant->next_of_kin_id = $next_of_kin->id;
                $applicant->save();
	}
}