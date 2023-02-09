<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Repositories\Interfaces\CampusInterface;

class FacutltyAction implements FacultyInterface{
	
	public function store(Request $request){
        $faculty = new Faculty;
        $faculty->faculty_name  = $request->get('faculty_name');
        $faculty->campus_id     = $request->get('campus');
        $faculty->save();
	    
	}

	public function update(Request $request){
	    
	}
}