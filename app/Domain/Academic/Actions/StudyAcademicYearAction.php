<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Repositories\Interfaces\StudyAcademicYearInterface;
use App\Utils\DateMaker;

class StudyAcademicYearAction implements StudyAcademicYearInterface{
	
	public function store(Request $request){
		$year = new StudyAcademicYear;
        $year->academic_year_id = $request->get('academic_year_id');
        $year->begin_date = DateMaker::toDBDate($request->get('begin_date'));
        $year->end_date = DateMaker::toDBDate($request->get('end_date'));
        $year->status = $request->get('status');
        $year->save();
	}

	public function update(Request $request){
		$year = StudyAcademicYear::find($request->get('study_academic_year_id'));
        $year->academic_year_id = $request->get('academic_year_id');
        $year->begin_date = DateMaker::toDBDate($request->get('begin_date'));
        $year->end_date = DateMaker::toDBDate($request->get('end_date'));
        $year->status = $request->get('status');
        $year->save();
	}
}