<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Repositories\Interfaces\FacultyInterface;

class FacultyAction implements FacultyInterface{
	
	public function store(Request $request){

                $faculty = new Faculty;
                $faculty->faculty_name          = $request->get('faculty_name');
                $faculty->faculty_abbreviation  = $request->get('faculty_abbreviation');
                $faculty->campus_id             = $request->get('campuses');
                $faculty->save();
	    
	}

	public function update(Request $request){
	    
	}
}