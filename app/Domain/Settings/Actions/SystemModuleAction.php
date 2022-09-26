<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\SystemModule;
use App\Domain\Settings\Repositories\Interfaces\SystemModuleInterface;

class SystemModuleAction implements SystemModuleInterface{
	
	public function store(Request $request){
	       $module = new SystemModule;
           $module->name = $request->get('name');
           $module->save();
	}

	public function update(Request $request){
	       $module = SystemModule::find($request->get('system_module_id'));
           $module->name = $request->get('name');
           $module->save();
	}
}