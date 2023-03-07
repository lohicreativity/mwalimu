<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Module;
use App\Domain\Academic\Repositories\Interfaces\ModuleInterface;
use App\Utils\SystemLocation;
use DB;

class ModuleAction implements ModuleInterface{
	
	public function store(Request $request){
            if($mod = Module::where('code',$request->get('code'))->where('name',$request->get('name'))->first()){
                    $module = $mod;
            }else{
		            $module = new Module;
                $module->name = $request->get('name');
                $module->code = $request->get('code');
                $module->credit = $request->get('credit');
                // $module->department_id = $request->get('department_id');
                $module->nta_level_id = $request->get('nta_level_id');
                $module->course_work_based = $request->has('course_work_based');
                if($request->hasFile('syllabus')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('syllabus')->move($destination, $request->file('syllabus')->getClientOriginalName());

                  // $file_name = SystemLocation::renameFile($destination, $request->file('syllabus')->getClientOriginalName(), $request->file('syllabus')->guessClientExtension());

                  $module->syllabus = $request->file('syllabus')->getClientOriginalName();
                }
                $module->save();
            }

                $existing_module_record = DB::table('module_department')
                ->where('module_id', $module->id)
                ->where('campus_id', $request->get('campus_id'))
                ->count();

                if ($existing_module_record == 0) {
                  $module->departments()->attach([$request->get('department_id')=>['campus_id'=>$request->get('campus_id')]]);
                }

	}

	public function update(Request $request){
		            $module = Module::find($request->get('module_id'));
                $module->name = $request->get('name');
                $module->code = $request->get('code');
                $module->credit = $request->get('credit');
                // $module->department_id = $request->get('department_id');
                $module->nta_level_id = $request->get('nta_level_id');
                $module->course_work_based = $request->get('course_work_based');
                if($request->hasFile('syllabus')){
                  $destination = SystemLocation::uploadsDirectory();
                  $request->file('syllabus')->move($destination, $request->file('syllabus')->getClientOriginalName());
                  // $file_name = SystemLocation::renameFile($destination, $request->file('syllabus')->getClientOriginalName(), $request->file('syllabus')->guessClientExtension());

                  $module->syllabus = $request->file('syllabus')->getClientOriginalName();
                }
                $module->save();

                $module->departments()->attach([$request->get('department_id')=>['campus_id'=>$request->get('campus_id')]]);
	}
}