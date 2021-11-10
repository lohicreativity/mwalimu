<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\staff;
use App\Domain\Academic\Repositories\Interfaces\StaffInterface;

class StaffAction implements StaffInterface{
	
	public function store(Request $request){
		$staff = new Staff;
                $staff->first_name = $request->get('first_name');
                $staff->middle_name = $request->get('middle_name');
                $staff->last_name = $request->get('surname');
                $staff->birth_date = $request->get('birth_date');
                $staff->qualification = $request->get('qualification');
                $staff->designation_id = $request->get('designation_id');
                $staff->gender = $request->get('gender');
                $table->category = $request->get('category');
                $table->type = $request->get('type');
                $table->phone = $request->get('phone');
                $table->email = $request->get('email');
                $table->address = $request->get('address');
                $table->staff_id = $request->get('staff_id');
                $table->disability_status = $request->get('disability_status');
                $staff->save();
	}

	public function update(Request $request){
		$staff = Staff::find($request->get('staff_id'));
                $staff->first_name = $request->get('first_name');
                $staff->middle_name = $request->get('middle_name');
                $staff->last_name = $request->get('surname');
                $staff->birth_date = $request->get('birth_date');
                $staff->qualification = $request->get('qualification');
                $staff->designation_id = $request->get('designation_id');
                $staff->gender = $request->get('gender');
                $table->category = $request->get('category');
                $table->type = $request->get('type');
                $table->phone = $request->get('phone');
                $table->email = $request->get('email');
                $table->address = $request->get('address');
                $table->staff_id = $request->get('staff_id');
                $table->disability_status = $request->get('disability_status');
                $staff->save();
	}
}