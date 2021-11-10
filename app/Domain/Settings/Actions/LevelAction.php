<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Level;
use App\Domain\Settings\Repositories\Interfaces\LevelInterface;

class LevelAction implements LevelInterface{
	
	public function store(Request $request){
	     $level = new Level;
         $level->name = $request->get('name');
         $level->save();
	}

	public function update(Request $request){
	     $level = Level::find($request->get('level_id'));
         $level->name = $request->get('name');
         $level->save();
	}
}