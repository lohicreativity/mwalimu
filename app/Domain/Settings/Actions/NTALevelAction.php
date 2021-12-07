<?php

namespace App\Domain\Settings\Actions;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Settings\Repositories\Interfaces\NTALevelInterface;

class NTALevelAction implements NTALevelInterface{
	
	public function store(Request $request){
	     $level = new NTALevel;
         $level->name = $request->get('name');
         $level->min_duration = $request->get('min_duration');
         $level->max_duration = $request->get('max_duration');
         $level->award_id = $request->get('award_id');
         $level->save();
	}

	public function update(Request $request){
	     $level = NTALevel::find($request->get('level_id'));
         $level->name = $request->get('name');
         $level->min_duration = $request->get('min_duration');
         $level->max_duration = $request->get('max_duration');
         $level->award_id = $request->get('award_id');
         $level->save();
	}
}