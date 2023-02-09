<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Faculty;
use App\Domain\Settings\Models\CampusFaculty;
use App\Models\User;
use App\Domain\Settings\Repositories\Interfaces\FacultyInterface;
use Auth;

class FacultyAction implements FacultyInterface{
	
	public function store(Request $request){

                $staff = User::find(Auth::user()->id)->staff;

                $faculty = new Faculty;
                $faculty->name          = $request->get('name');
                $faculty->abbreviation  = $request->get('abbreviation');
                if (Auth::user()->hasRole('administrator')) {
                        $faculty->campus_id     = $request->get('campuses');
                } else if (Auth::user()->hasRole('admission-officer')) {
                        $faculty->campus_id = $staff->campus_id;      
                }
                $faculty->save();

                if (Auth::user()->hasRole('administrator')) {
                        $faculty->campuses()->sync($request->get('campuses'));
                } else if (Auth::user()->hasRole('admission-officer')) {
                        $faculty->campuses()->sync($staff->campus_id);
                }
                
	    
	}

	public function update(Request $request){

                $faculty = Faculty::find($request->get('faculty_id'));
                $faculty->name          = $request->get('name');
                $faculty->abbreviation  = $request->get('abbreviation');
                $faculty->campus_id     = $request->get('campus');
                $faculty->save();

                // $campus_faculty = CampusFaculty::where('campus_id', $request->get('campus'))
                // ->where('faculty_id', $request->get('faculty_id'))
                // ->update(['campus_id' => $request->get('campus')]);
	    
	}
}