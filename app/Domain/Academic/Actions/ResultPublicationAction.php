<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ResultPublication;
use App\Domain\Academic\Repositories\Interfaces\ResultPublicationInterface;
use Auth;

class ResultPublicationAction implements ResultPublicationInterface{
	
	public function store(Request $request){
		$publication = new ResultPublication;
                $publication->study_academic_year_id = $request->get('study_academic_year_id');
                $publication->semester_id = $request->get('semester_id');
                $publication->campus_id = $request->get('campus_id');
                $publication->nta_level_id = $request->get('nta_level_id');
                $publication->status = $request->get('status');
                $publication->published_by_user_id = Auth::user()->id;
                $publication->published_at = now();
                $publication->save();
	}

	public function update(Request $request){
		$award = ResultPublication::find($request->get('result_publication_id'));
                $publication->study_academic_year_id = $request->get('study_academic_year_id');
                $publication->semester_id = $request->get('semester_id');
                $publication->campus_id = $request->get('campus_id');
                $publication->nta_level_id = $request->get('nta_level_id');
                $publication->status = $request->get('status');
                $publication->published_by_user_id = Auth::user()->id;
                $publication->published_at = now();
                $publication->save();
	}
}