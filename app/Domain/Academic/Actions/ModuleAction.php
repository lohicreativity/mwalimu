<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Repositories\Interfaces\ModuleInterface;

class ModuleAction implements ModuleInterface{
	
	public function store(Request $request){
		$module = new Module;
                $module->name = $request->get('name');
                $module->code = $request->get('code');
                $module->credit = $request->get('credit');
                $module->course_work = $request->get('course_work');
                $module->final_exam = $request->get('final_exam');
                $module->department_id = $request->get('department_id');
                $module->save();
	}

	public function update(Request $request){
		$module = Module::find($request->get('module_id'));
                $module->name = $request->get('name');
                $module->code = $request->get('code');
                $module->credit = $request->get('credit');
                $module->course_work = $request->get('course_work');
                $module->final_exam = $request->get('final_exam');
                $module->department_id = $request->get('department_id');
                $module->save();
	}
}