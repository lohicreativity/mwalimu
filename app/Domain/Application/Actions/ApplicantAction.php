<?php

namespace App\Domain\Application\Actions;

use Illuminate\Http\Request;
use App\Domain\Application\Models\Applicant;
use App\Domain\Application\Models\HealthInsurance;
use App\Domain\Application\Repositories\Interfaces\ApplicantInterface;
use App\Utils\DateMaker;
use App\Utils\SystemLocation;

class ApplicantAction implements ApplicantInterface{
	
	public function store(Request $request){
		$applicant = new Applicant;
                $applicant->first_name = $request->get('first_name');
                $applicant->middle_name = $request->get('middle_name');
                $applicant->last_name = $request->get('last_name');
                $applicant->birth_date = $request->get('birth_date');
                $applicant->nationality = $request->get('nationality');
                $applicant->gender = $request->get('gender');

                $applicant->save();
	}

	public function update(Request $request){

        $phone = substr($request->get('phone'), 1);
        $phone = '255'.$phone;

		$applicant = Applicant::find($request->get('applicant_id'));
        $applicant->first_name = $request->get('first_name');
        $applicant->middle_name = $request->get('middle_name');
        $applicant->surname = $request->get('surname');
        $applicant->email = $request->get('email');
        $applicant->phone = $phone;
        // $applicant->phone = str_replace('+','',$request->get('phone'));
        $applicant->birth_date = $request->get('year').'-'.$request->get('month').'-'.$request->get('date');
        $applicant->nationality = $request->get('nationality');
        $applicant->gender = $request->get('sex');
        $applicant->disability_status_id = $request->get('disability_status_id');
        $applicant->address = 'P. O. Box '.$request->get('address');
        $applicant->country_id = $request->get('country_id');
        $applicant->region_id = $request->get('region_id');
        $applicant->district_id = $request->get('district_id');
        $applicant->ward_id = $request->get('ward_id');
        $applicant->street = strtoupper($request->get('street'));
        $applicant->basic_info_complete_status = 1;
        $applicant->save();

        $other_apps = Applicant::where('user_id',$applicant->user_id)->where('campus_id','!=',$applicant->campus_id)->get();
        foreach ($other_apps as $appl) {
            # code...
            $app = Applicant::find($appl->id);
            $app->first_name = $applicant->first_name;
            $app->middle_name = $applicant->middle_name;
            $app->surname = $applicant->surname;
            $app->email = $applicant->email;
            $app->phone = $applicant->phone;
            $app->birth_date = $applicant->birth_date;
            $app->nationality = $applicant->nationality;
            $app->gender = $applicant->gender;
            $app->disability_status_id = $applicant->disability_status_id;
            $app->address = $applicant->address;
            $app->country_id = $applicant->country_id;
            $app->region_id = $applicant->region_id;
            $app->district_id = $applicant->district_id;
            $app->ward_id = $applicant->ward_id;
            $app->street = $applicant->street;
            $app->basic_info_complete_status = $applicant->basic_info_complete_status;
            $app->save();
        }
	}

        /**
         * Upload documents
         */
        public function uploadDocuments(Request $request)
        {
            $applicant = Applicant::with('programLevel')->find($request->get('applicant_id'));
            $name = $applicant->first_name.'_'.$applicant->middle_name.'_'.$applicant->surname.'_'.time();

            if($request->hasFile('document')){
                $destination = SystemLocation::uploadsDirectory();
                $request->file('document')->move($destination, $request->file('document')->getClientOriginalName());
                $file_name = SystemLocation::renameFile($destination, $request->file('document')->getClientOriginalName(), $request->file('document')->guessClientExtension(),$name);
                if($request->get('document_name') == 'birth_certificate'){
                    $applicant->birth_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                }

                if($request->get('document_name') == 'o_level_certificate'){
                    $applicant->o_level_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                }
 
                if($request->get('document_name') == 'basic_certificate'){
                    $applicant->nacte_reg_no = $file_name; //$request->file('document')->getClientOriginalName();
                }

                if($request->get('document_name') == 'a_level_certificate'){
                    $applicant->a_level_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                }

                if($request->get('document_name') == 'diploma_certificate'){
                    $applicant->diploma_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

                if($request->get('document_name') == 'nva_certificate'){
                    $applicant->nva_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

                if($request->get('document_name') == 'passport'){
                    $applicant->passport_picture = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

                if($request->get('document_name') == 'teacher_diploma_certificate'){
                    $applicant->teacher_diploma_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

                if($request->get('document_name') == 'veta_certificate'){
                    $applicant->veta_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

                if($request->get('document_name') == 'degree_certificate'){
                    $applicant->degree_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                }
                
                if($request->get('document_name') == 'degree_transcript'){
                    $applicant->degree_transcript = $file_name; //$request->file('document')->getClientOriginalName(); 
                }

            }

            if($applicant->entry_mode == 'DIRECT'){
                if(str_contains($applicant->programLevel->name,'Bachelor')){
                    if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }

                }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                    if($applicant->birth_certificate && $applicant->o_level_certificate){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }

                }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){
                    if($applicant->status == null){
                        if($applicant->o_level_certificate && $applicant->a_level_certificate 
                            && $applicant->degree_certificate && $applicant->degree_transcript){
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }

                    }elseif($applicant->status == 'ADMITTED'){
                        if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->a_level_certificate 
                            && $applicant->degree_certificate && $applicant->degree_transcript){
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }
                    }
                }
            }else{

                // if(str_contains($applicant->programLevel->name,'Bachelor')){
                //     if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->diploma_certificate){
                //         $applicant->documents_complete_status = 1;
                //     }else{
                //         $applicant->documents_complete_status = 0;
                //     }
                // }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                //     if($applicant->birth_certificate && $applicant->o_level_certificate){
                //         $applicant->documents_complete_status = 1;
                //     }else{
                //         $applicant->documents_complete_status = 0;
                //     }
                // }

                
                if(str_contains($applicant->programLevel->name,'Bachelor')){

                    if($applicant->status == null){
                        if($applicant->diploma_certificate || $applicant->teacher_diploma_certificate){
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }
                    }elseif($applicant->status == 'ADMITTED'){
                        if($applicant->birth_certificate && $applicant->o_level_certificate && ($applicant->a_level_certificate || $applicant->diploma_certificate)){
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }
                    }
                    
                }elseif(str_contains($applicant->programLevel->name,'Diploma') || str_contains($applicant->programLevel->name,'Certificate')){
                    if($applicant->birth_certificate && $applicant->o_level_certificate){
                        $applicant->documents_complete_status = 1;
                    }else{
                        $applicant->documents_complete_status = 0;
                    }
                }elseif(str_contains(strtolower($applicant->programLevel->name),'master')){
                    if($applicant->status == null) {
                        if($applicant->o_level_certificate && $applicant->diploma_certificate 
                            && $applicant->degree_certificate && $applicant->degree_transcript) {
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }

                    }elseif($applicant->status == 'ADMITTED') {

                        if($applicant->birth_certificate && $applicant->o_level_certificate && $applicant->diploma_certificate 
                            && $applicant->degree_certificate && $applicant->degree_transcript){
                            $applicant->documents_complete_status = 1;
                        }else{
                            $applicant->documents_complete_status = 0;
                        }
                    }
                }
            }

            $applicant->save();

            $other_apps = Applicant::where('user_id',$applicant->user_id)->where('campus_id','!=',$applicant->campus_id)->get();
            foreach ($other_apps as $appl) {
                # code...
                $app = Applicant::find($appl->id);

                if($request->hasFile('document')){
                // $file_name = SystemLocation::renameFile($destination, $request->file('image')->getClientOriginalName(), $request->file('image')->guessClientExtension());
                    if($request->get('document_name') == 'birth_certificate'){
                        $app->birth_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                    }

                    if($request->get('document_name') == 'o_level_certificate'){
                        $app->o_level_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                    }
                    
                    if($request->get('document_name') == 'a_level_certificate'){
                        $app->a_level_certificate = $file_name; //$request->file('document')->getClientOriginalName();
                    }

                    if($request->get('document_name') == 'diploma_certificate'){
                        $app->diploma_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }

                    if($request->get('document_name') == 'nva_certificate'){
                        $app->nva_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }

                    if($request->get('document_name') == 'passport'){
                       $app->passport_picture = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }

                    if($request->get('document_name') == 'teacher_diploma_certificate'){
                       $app->teacher_diploma_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }

                    if($request->get('document_name') == 'degree_certificate'){
                        $applicant->degree_certificate = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }

                    if($request->get('document_name') == 'degree_transcript'){
                        $applicant->degree_transcript = $file_name; //$request->file('document')->getClientOriginalName(); 
                    }
                }

                $app->documents_complete_status = $applicant->documents_complete_status;

                $app->save();
            }
        }
		
		public function uploadInsurance(Request $request)
		{		
			$insurance = new HealthInsurance;
			$insurance->insurance_name = $request->get('insurance_name');
			$insurance->applicant_id = $request->get('applicant_id');
			$insurance->membership_number = $request->get('card_number');
			$insurance->expire_date = $request->get('expire_year').'-'.$request->get('expire_month').'-'.$request->get('expire_date');
			if($request->hasFile('insurance_card')){
				  $destination = SystemLocation::uploadsDirectory();
				  $request->file('insurance_card')->move($destination, $request->file('insurance_card')->getClientOriginalName());

				  $insurance->card = $request->file('insurance_card')->getClientOriginalName();
			}

			$insurance->save();
		}
}