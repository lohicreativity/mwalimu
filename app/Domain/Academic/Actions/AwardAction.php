<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Repositories\Interfaces\AwardInterface;

class AwardAction implements AwardInterface{
	
	public function store(Request $request){
		$award = new Award;
                $award->name = $request->get('name');
                $award->code = $request->get('code');
                $award->level_id = $request->get('level_id');
                $award->save();
	}

	public function update(Request $request){
		$award = Award::find($request->get('award_id'));
                $award->name = $request->get('name');
                $award->code = $request->get('code');
                $award->level_id = $request->get('level_id');
                $award->save();
	}
}