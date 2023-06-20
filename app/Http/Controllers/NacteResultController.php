<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\OutResultDetail;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\Applicant;
use Illuminate\Support\Facades\Http;
use App\Domain\Application\Models\ApplicantProgramSelection;

class NacteResultController extends Controller
{
    /**
     *  Confirm NECTA results
     */
    public function confirm(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
        $detail->verified = 1;
        $detail->save();
        $applicant  = Applicant::find($request->get('applicant_id'));
        if(strtoupper($applicant->first_name) != strtoupper($detail->firstname) || strtoupper($applicant->surname) != strtoupper($detail->surname)){
            return redirect()->to('application/nullify-nacte-results?detail_id='.$request->get('nacte_result_detail_id'));
        }
        $results_count = NacteResult::where('nacte_result_detail_id',$request->get('nacte_result_detail_id'))->count();
        $out_count = OutResultDetail::where('applicant_id',$applicant->id)->count();
        $o_level_result_count = NectaResultDetail::where('applicant_id',$applicant->id)->where('exam_id',1)->count();
        $a_level_result_count = NectaResultDetail::where('applicant_id',$applicant->id)->where('exam_id',2)->count();
        $diploma_result_count = NacteResultDetail::where('applicant_id',$applicant->id)->count();
        $applicant->first_name = $detail->firstname;
        $applicant->middle_name =  $detail->middlename;
        $applicant->surname = $detail->surname;
        // $applicant->gender = $detail->gender;
        if($applicant->entry_mode == 'EQUIVALENT'){
           $applicant->avn_no_results = $results_count == 0? 1 : 0;
        }
        if(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'EQUIVALENT' && $detail->diploma_gpa >= 3 && $o_level_result_count != 0){
            $applicant->results_complete_status = 1;
        }elseif(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'EQUIVALENT' && $out_count != 0 && $o_level_result_count != 0){
            if(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'EQUIVALENT' && $out_count != 0 && $a_level_result_count != 0){
                $applicant->results_complete_status = 1;
            }elseif(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'EQUIVALENT' && $out_count != 0 && $diploma_result_count != 0){
                $applicant->results_complete_status = 1;
            }elseif(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'EQUIVALENT' && $out_count != 0 && $applicant->teacher_certificate_status == 1){
                $applicant->results_complete_status = 1;
            }
        }elseif(str_contains($applicant->programLevel->name,'Diploma') && $applicant->entry_mode == 'EQUIVALENT' && $out_count != 0 && $o_level_result_count != 0){
            $applicant->results_complete_status = 1;
        }
        $applicant->save();

        return redirect()->back()->with('message','NACTE results confirmed successfully');
    }

    public function confirmNacteRegNumber(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $nacte_detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));

        $applicant = Applicant::find($request->get('applicant_id'));

        //if($applicant->nacte_reg_no != $nacte_detail->registration_number){

        if(strtoupper($applicant->first_name) != strtoupper($nacte_detail->firstname) || strtoupper($applicant->surname) != strtoupper($nacte_detail->surname)){
            return redirect()->to('application/nullify-nacte-reg-results?detail_id='.$request->get('nacte_result_detail_id'));
           // }
            

        }else{

            $applicant->nacte_reg_no = $request->get('nacte_reg_no');
            
            if(NectaResultDetail::where('applicant_id',$applicant->id)->where('verified',1)->count() != 0){
               $applicant->results_complete_status = 1;
            }

            $applicant->save();
            $nacte_detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
            $nacte_detail->verified = 1;
            $nacte_detail->save();
            
            return redirect()->back()->with('message','NACTE results confirmed successfully');

        }
    }

    public function declineNacteRegNumber(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
        if($detail->verified != 1){
    	    $detail->delete();
        }
	    return redirect()->back()->with('message','NACTE results declined successfully');
    }

    /**
     * Nullify NACTE results
     */
    public function nullifyNacteReg(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $detail = NacteResultDetail::find($request->get('detail_id'));
        $detail->delete();
        return redirect()->back()->with('error','NECTA results names do not match your application names');
    }

    /**
     * Delete NACTE results
     */
    public function destroy(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

    	$detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
        if($detail->verified != 1){
    	NacteResult::where('nacte_result_detail_id',$request->get('nacte_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
        }
	    return redirect()->back()->with('message','NACTE results declined successfully');
    }

    /**
     * Delete NACTE results
     */
    public function nullify(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }
        
        $detail = NacteResultDetail::find($request->get('detail_id'));
        NacteResult::where('nacte_result_detail_id',$request->get('detail_id'))->delete();
        // $detail->results->delete();
        $detail->delete();
        return redirect()->back()->with('error','NACTE results names do not match your application names');
    }
}
