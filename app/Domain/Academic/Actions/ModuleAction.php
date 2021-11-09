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
        $module->save();
	}

	public function update(Request $request){
		$module = Module::find($request->get('module_id'));
        $module->name = $request->get('name');
        $module->code = $request->get('code');
        $module->credit = $request->get('credit');
        $module->save();
	}
}