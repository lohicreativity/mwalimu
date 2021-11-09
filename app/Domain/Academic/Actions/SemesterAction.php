<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Semester;
use App\Domain\Academic\Repositories\Interfaces\SemesterInterface;

class SemesterAction implements SemesterInterface{
	
	public function store(Request $request){
		$semester = new Semester;
        $semester->name = $request->get('name');
        $semester->save();
	}

	public function update(Request $request){
		$semester = Semester::find($request->get('semester_id'));
        $semester->name = $request->get('name');
        $semester->save();
	}
}