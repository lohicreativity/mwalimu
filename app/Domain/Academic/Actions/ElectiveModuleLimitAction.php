<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\ElectiveModuleLimit;
use App\Domain\Academic\Repositories\Interfaces\ElectiveModuleLimitInterface;
use App\Utils\DateMaker;

class ElectiveModuleLimitAction implements ElectiveModuleLimitInterface{
	
	public function store(Request $request){
		$limit = new ElectiveModuleLimit;
                $limit->campus_id = $request->get('campus_id');
                $limit->study_academic_year_id = $request->get('study_academic_year_id');
                $limit->semester_id = $request->get('semester_id');
                $limit->award_id = $request->get('award_id');
                $limit->deadline = DateMaker::toDBDate($request->get('deadline'));
                $limit->save();
	}

	public function update(Request $request){
		$limit = ElectiveModuleLimit::find($request->get('elective_module_limit_id'));
                $limit->campus_id = $request->get('campus_id');
                $limit->study_academic_year_id = $request->get('study_academic_year_id');
                $limit->semester_id = $request->get('semester_id');
                $limit->award_id = $request->get('award_id');
                $limit->deadline = DateMaker::toDBDate($request->get('deadline'));
                $limit->save();
	}
}