<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Repositories\Interfaces\PostponementInterface;

class PostponementAction implements PostponementInterface{
	
	public function store(Request $request){
		$postponement = new Postponement;
        $postponement->study_academic_year_id = $request->get('study_academic_year_id');
        $postponement->student_id = $request->get('student_id');
        $postponement->semester_id = $request->get('semester_id');
        $postponement->category = $request->get('category');
        $postponement->status = $request->get('status');
        $postponement->save();
	}

	public function update(Request $request){
		$postponement = Postponement::find($request->get('postponement_id'));
        $postponement->study_academic_year_id = $request->get('study_academic_year_id');
        $postponement->student_id = $request->get('student_id');
        $postponement->semester_id = $request->get('semester_id');
        $postponement->category = $request->get('category');
        $postponement->status = $request->get('status');
        $postponement->save();
	}
}