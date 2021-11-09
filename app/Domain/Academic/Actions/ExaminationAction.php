<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Examination;
use App\Domain\Academic\Repositories\Interfaces\ExaminationInterface;

class ExaminationAction implements ExaminationInterface{
	
	public function store(Request $request){
		$examination = new Examination;
        $examination->name = $request->get('name');
        $examination->save();
	}

	public function update(Request $request){
		$examination = Examination::find($request->get('department_id'));
        $examination->name = $request->get('name');
        $examination->save();
	}
}