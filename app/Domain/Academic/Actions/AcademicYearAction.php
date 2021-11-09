<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\AcademicYear;
use App\Domain\Academic\Repositories\Interfaces\AcademicYearInterface;

class AcademicYearAction implements AcademicYearInterface{
	
	public function store(Request $request){
		$academic_year = new AcademicYear;
        $academic_year->year = $request->get('year');
        $academic_year->save();
	}

	public function update(Request $request){
		$academic_year = AcademicYear::find($request->get('academic_year_id'));
        $academic_year->year = $request->get('year');
        $academic_year->save();
	}
}