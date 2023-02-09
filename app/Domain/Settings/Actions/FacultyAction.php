<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Repositories\Interfaces\FacultyInterface;

class FacultyAction implements FacultyInterface{
	
	public function store(Request $request){

                $faculty = new Faculty;
                $faculty->name          = $request->get('faculty_name');
                $faculty->abbreviation  = $request->get('faculty_abbreviation');
                $faculty->campus_id     = $request->get('campuses');
                $faculty->save();
	    
	}

	public function update(Request $request){
	    
	}
}