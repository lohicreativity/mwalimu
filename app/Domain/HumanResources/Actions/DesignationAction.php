<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\designation;
use App\Domain\Academic\Repositories\Interfaces\DesignationInterface;

class DesignationAction implements designationInterface{
	
	public function store(Request $request){
		$designation = new Designation;
        $designation->name = $request->get('name');
        $designation->save();
	}

	public function update(Request $request){
		$designation = Designation::find($request->get('designation_id'));
        $designation->name = $request->get('name');
        $designation->save();
	}
}