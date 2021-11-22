<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\StreamComponent;
use App\Domain\Registration\Models\Registration;
use App\Domain\Academic\Repositories\Interfaces\StreamComponentInterface;

class StreamComponentAction implements StreamComponentInterface{
	
	public function store(Request $request){

        $reg_count =  Registration::where('year_of_study',$request->get('year_of_study'))->where('study_academic_year_id',$request->get('study_academic_year_id'))->count();
        $component = new StreamComponent;
        $component->number_of_students = $reg_count;
        $component->number_of_streams = $request->get('number_of_streams');
        $component->study_academic_year_id = $request->get('study_academic_year_id');
        $component->campus_program_id = $request->get('campus_program_id');
        $component->year_of_study = $request->get('year_of_study');
        $component->save();

        return $component;
	     
	}
}