<?php

namespace App\Domain\HumanResources\Actions;

use Illuminate\Http\Request;
use App\Domain\HumanResources\Models\Staff;
use App\Models\User;
use App\Models\Role;
use App\Domain\HumanResources\Repositories\Interfaces\StaffInterface;
use App\Utils\DateMaker;
use App\Utils\SystemLocation;
use DB, Hash;

class StaffAction implements StaffInterface{
	
	public function store(Request $request){
                DB::beginTransaction();

                $user = new User;
                $user->name = $request->get('first_name').' '.$request->get('surname');
                $user->email = $request->get('email');
                $user->password = Hash::make('123456');
                $user->email_verified_at = now();
                $user->must_update_password = 1;
                $user->save();

                $role = Role::where('name','staff')->first();
                $user->roles()->sync([$role->id]);

		$staff = new Staff;
                $staff->first_name = $request->get('first_name');
                $staff->middle_name = $request->get('middle_name');
                $staff->surname = $request->get('surname');
                $staff->birth_date = DateMaker::toDBDate($request->get('birth_date'));
                $staff->qualification = $request->get('qualification');
                $staff->designation_id = $request->get('designation_id');
                $staff->gender = $request->get('gender');
                $staff->category = $request->get('category');
                $staff->phone = $request->get('phone');
                $staff->email = $request->get('email');
                $staff->address = $request->get('address');
                $staff->nin = $request->get('nin');
                $staff->pf_number = $request->get('pf_number');
                $staff->vote_number = $request->get('vote_number');
                $staff->check_number = $request->get('check_number');
                $staff->block = $request->get('block');
                $staff->floor = $request->get('floor');
                $staff->schedule = $request->get('schedule');
                $staff->campus_id = $request->get('campus_id');
                $staff->country_id = $request->get('country_id');
                $staff->region_id = $request->get('region_id');
                $staff->district_id = $request->get('district_id');
                $staff->ward_id = $request->get('ward_id');
                $staff->street = $request->get('street');
                $staff->disability_status_id = $request->get('disability_status_id');
                $staff->user_id = $user->id;
                if($request->hasFile('image')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('image')->move($destination, $request->file('image')->getClientOriginalName());
                  $file_name = SystemLocation::renameFile($destination, $request->file('image')->getClientOriginalName(), $request->file('image')->guessClientExtension());

                  $staff->image = $file_name;
                }
                $staff->save();
                DB::commit();
	}

	public function update(Request $request){
                DB::beginTransaction();

		$staff = Staff::find($request->get('staff_id'));
                $staff->first_name = $request->get('first_name');
                $staff->middle_name = $request->get('middle_name');
                $staff->surname = $request->get('surname');
                $staff->birth_date = DateMaker::toDBDate($request->get('birth_date'));
                $staff->qualification = $request->get('qualification');
                $staff->designation_id = $request->get('designation_id');
                $staff->gender = $request->get('gender');
                $staff->category = $request->get('category');
                $staff->phone = $request->get('phone');
                $staff->email = $request->get('email');
                $staff->address = $request->get('address');
                $staff->nin = $request->get('nin');
                $staff->pf_number = $request->get('pf_number');
                $staff->vote_number = $request->get('vote_number');
                $staff->check_number = $request->get('check_number');
                $staff->block = $request->get('block');
                $staff->floor = $request->get('floor');
                $staff->schedule = $request->get('schedule');
                $staff->campus_id = $request->get('campus_id');
                $staff->country_id = $request->get('country_id');
                $staff->region_id = $request->get('region_id');
                $staff->district_id = $request->get('district_id');
                $staff->ward_id = $request->get('ward_id');
                $staff->street = $request->get('street');
                $staff->disability_status_id = $request->get('disability_status_id');
                if($request->hasFile('image')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('image')->move($destination, $request->file('image')->getClientOriginalName());
                  $file_name = SystemLocation::renameFile($destination, $request->file('image')->getClientOriginalName(), $request->file('image')->guessClientExtension());

                  $staff->image = $file_name;
                }
                $staff->save();

                $user = User::find($staff->user_id);
                $user->name = $staff->first_name.' '.$staff->surname;
                $user->save();
                DB::commit();
	}
}