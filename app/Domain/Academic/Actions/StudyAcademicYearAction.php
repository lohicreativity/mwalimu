<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Repositories\Interfaces\StudyAcademicYearInterface;
use App\Utils\DateMaker;

class StudyAcademicYearAction implements StudyAcademicYearInterface{
	
	public function store(Request $request){
		      $year = new StudyAcademicYear;
          if($ac_year = AcademicYear::where('year',$request->get('academic_year_id'))->first()){
             $academic_year = $ac_year;
          }else{
             $academic_year = new AcademicYear;
             $academic_year->year = $request->get('academic_year_id');
             $academic_year->save();
          }
          $year->academic_year_id = $academic_year->id;
          $year->begin_date = DateMaker::toDBDate($request->get('begin_date'));
          $year->end_date = DateMaker::toDBDate($request->get('end_date'));
          $year->status = $request->get('status');
          $year->save();
	}

	public function update(Request $request){
		$year = StudyAcademicYear::find($request->get('study_academic_year_id'));
                if($ac_year = AcademicYear::where('year',$request->get('academic_year_id'))->first()){
                   $academic_year = $ac_year;
                }else{
                   $academic_year = new AcademicYear;
                   $academic_year->year = $request->get('academic_year_id');
                   $academic_year->save();
                }
                $year->academic_year_id = $academic_year->id;
                $year->begin_date = DateMaker::toDBDate($request->get('begin_date'));
                $year->end_date = DateMaker::toDBDate($request->get('end_date'));
                $year->status = $request->get('status');
                $year->save();
	}
}