<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Repositories\Interfaces\FacultyInterface;

class FacultyAction implements FacultyInterface{
	
	public function store(Request $request){

                $faculty = new Faculty;
                $faculty->name          = $request->get('name');
                $faculty->abbreviation  = $request->get('abbreviation');
                $faculty->campus_id     = $request->get('campuses');
                $faculty->save();

                $faculty->campuses()->sync($request->get('campuses'));
	    
	}

	public function update(Request $request){
	    
	}
}