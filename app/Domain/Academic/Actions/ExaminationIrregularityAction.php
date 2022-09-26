<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ExaminationIrregularity;
use App\Domain\Academic\Repositories\Interfaces\ExaminationIrregularityInterface;
use App\Utils\DateMaker;

class ExaminationIrregularityAction implements ExaminationIrregularityInterface{
	
	public function store(Request $request){
		$irregularity = new ExaminationIrregularity;
                $irregularity->student_id = $request->get('student_id');
                $irregularity->study_academic_year_id = $request->get('study_academic_year_id');
                $irregularity->semester_id = $request->get('semester_id');
                $irregularity->module_assignment_id = $request->get('module_assignment_id');
                $irregularity->description = $request->get('description');
                $irregularity->save();
	}

	public function update(Request $request){
		$irregularity = ExaminationIrregularity::find($request->get('examination_irregularity_id'));
                $irregularity->student_id = $request->get('student_id');
                $irregularity->study_academic_year_id = $request->get('study_academic_year_id');
                $irregularity->semester_id = $request->get('semester_id');
                $irregularity->module_assignment_id = $request->get('module_assignment_id');
                $irregularity->description = $request->get('description');
                $irregularity->save();
	}
}