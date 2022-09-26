<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Intake;
use App\Domain\Settings\Repositories\Interfaces\IntakeInterface;

class IntakeAction implements IntakeInterface{
	
	public function store(Request $request){
	     $intake = new Intake;
           $intake->name = $request->get('name');
           $intake->save();
	}

	public function update(Request $request){
	     $intake = Intake::find($request->get('intake_id'));
           $intake->name = $request->get('name');
           $intake->save();
	}
}