<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Campus;
use App\Domain\Settings\Repositories\Interfaces\CampusInterface;

class CampusAction implements CampusInterface{
	
	public function store(Request $request){
	     $campus = new Campus;
             $campus->name = $request->get('name');
             $campus->abbreviation = $request->get('abbreviation');
             $campus->email = $request->get('email');
             $campus->phone = $request->get('phone');
             $campus->region_id = $request->get('region_id');
             $campus->district_id = $request->get('district_id');
             $campus->ward_id = $request->get('ward_id');
             $campus->street = $request->get('street');
             $campus->save();
	}

	public function update(Request $request){
	     $campus = campus::find($request->get('campus_id'));
             $campus->name = $request->get('update-name');
             $campus->abbreviation = $request->get('update-abbreviation');
             $campus->email = $request->get('update-email');
             $campus->phone = $request->get('update-phone');
             $campus->region_id = $request->get('region_id');
             $campus->district_id = $request->get('district_id');
             $campus->ward_id = $request->get('ward_id');
             $campus->street = $request->get('update-street');
             $campus->save();
	}
}