<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\CourseWorkComponent;
use App\Domain\Academic\Repositories\Interfaces\CourseWorkComponentInterface;

class CourseWorkComponentAction implements CourseWorkComponentInterface{
	
	public function store(Request $request){
                if($request->get('tests') != 0){
        	      $component = new CourseWorkComponent;
                      $component->name = 'Test';
                      $component->quantity = $request->get('tests');
                      $component->module_assignment_id = $request->get('module_assignment_id');
                      $component->save();
                }

                if($request->get('assignments') != 0){
                      $component = new CourseWorkComponent;
                      $component->name = 'Assignment';
                      $component->quantity = $request->get('assignments');
                      $component->module_assignment_id = $request->get('module_assignment_id');
                      $component->save();
                }

                if($request->get('quizes') != 0){
                      $component = new CourseWorkComponent;
                      $component->name = 'Quiz';
                      $component->quantity = $request->get('quizes');
                      $component->module_assignment_id = $request->get('module_assignment_id');
                      $component->save();
                }
	}

}