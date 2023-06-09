<?php

namespace App\Domain\Academic\Actions;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Repositories\Interfaces\PostponementInterface;
use App\Utils\SystemLocation;

class PostponementAction implements PostponementInterface{
	
	public function store(Request $request){
		$postponement = new Postponement;
                $postponement->study_academic_year_id = $request->get('study_academic_year_id');
                $postponement->student_id = $request->get('student_id');
                $postponement->semester_id = $request->get('semester_id');
                $postponement->category = $request->get('category');
                $postponement->status = $request->get('status');
                if($request->hasFile('postponement_letter')){
                      $destination = SystemLocation::uploadsDirectory();
                      $request->file('postponement_letter')->move($destination, $request->file('postponement_letter')->getClientOriginalName());

                      $postponement->letter = $request->file('postponement_letter')->getClientOriginalName();
                }
                if($request->hasFile('supporting_document')){
                      $destination = SystemLocation::uploadsDirectory();
                      $request->file('supporting_document')->move($destination, $request->file('supporting_document')->getClientOriginalName());

                      $postponement->supporting_document = $request->file('supporting_document')->getClientOriginalName();
                }
                if(Postponement::where('student_id',$request->get('student_id'))->where('status','!=','RESUMED')->count() != 0){
                     $postponement->is_renewal = 1;
                 }

                $postponement->save();
	}

	public function update(Request $request){
		$postponement = Postponement::find($request->get('postponement_id'));
                $postponement->study_academic_year_id = $request->get('study_academic_year_id');
                $postponement->student_id = $request->get('student_id');
                $postponement->semester_id = $request->get('semester_id');
                $postponement->category = $request->get('category');
                $postponement->status = $request->get('status');
                if($request->hasFile('postponement_letter')){
                      $destination = SystemLocation::uploadsDirectory();
                      $request->file('postponement_letter')->move($destination, $request->file('postponement_letter')->getClientOriginalName());

                      $postponement->letter = $request->file('postponement_letter')->getClientOriginalName();
                }
                if($request->hasFile('supporting_document')){
                      $destination = SystemLocation::uploadsDirectory();
                      $request->file('supporting_document')->move($destination, $request->file('supporting_document')->getClientOriginalName());

                      $postponement->supporting_document = $request->file('supporting_document')->getClientOriginalName();
                }
                $postponement->save();
	}
}