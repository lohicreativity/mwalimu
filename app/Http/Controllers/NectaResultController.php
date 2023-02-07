<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use App\Domain\Application\Models\Applicant;
use Illuminate\Support\Facades\Http;

class NectaResultController extends Controller
{

    /**
     *  Confirm NECTA results
     */
    public function confirm(Request $request)
    {
        $detail = NectaResultDetail::find($request->get('necta_result_detail_id'));
        $detail->verified = 1;
        $detail->save();
        $applicant  = Applicant::find($request->get('applicant_id'));
        $non_details = NectaResultDetail::where('id','!=',$request->get('necta_result_detail_id'))->where('first_name','!=',$detail->first_name)->where('last_name','!=',$detail->last_name)->get();

        // if(count($non_details) != 0){
        //     return redirect()->to('application/nullify-necta-results?detail_id='.$request->get('necta_result_detail_id'));
        // }
        // if($detail->exam_id == 2){
        if($applicant->index_number != $detail->index_number){
            if(strtoupper($applicant->first_name) != strtoupper($detail->first_name) || strtoupper($applicant->surname) != strtoupper($detail->last_name)){
                return redirect()->to('application/nullify-necta-results?detail_id='.$request->get('necta_result_detail_id'));
            }
        }
        $applicant->first_name = $detail->first_name;
        $applicant->middle_name =  $detail->middle_name;
        $applicant->surname = $detail->last_name;
        return $detail->gender;
        if(str_contains($applicant->programLevel->name,'Bachelor') && $applicant->entry_mode == 'DIRECT' && $detail->exam_id == 2){
            $applicant->results_complete_status = 1;
        }elseif(str_contains($applicant->programLevel->name,'Diploma') && $applicant->entry_mode == 'DIRECT' && $detail->exam_id == 1){
            $applicant->results_complete_status = 1;
        }elseif(str_contains($applicant->programLevel->name,'Certificate') && $applicant->entry_mode == 'DIRECT' && $detail->exam_id == 1){
            $applicant->results_complete_status = 1;
        }
        $applicant->save();

        return redirect()->back()->with('message','NECTA results confirmed successfully');
    }
    /**
     * Store NECTA results
     */
    public function destroy(Request $request)
    {
    	$detail = NectaResultDetail::find($request->get('necta_result_detail_id'));
        if($detail->verified != 1){
        	NectaResult::where('necta_result_detail_id',$request->get('necta_result_detail_id'))->delete();
        	// $detail->results->delete();
        	$detail->delete();
        }
	    return redirect()->back()->with('message','NECTA results declined successfully');
    }

    /**
     * Nullify NECTA results
     */
    public function nullify(Request $request)
    {
        $detail = NectaResultDetail::find($request->get('detail_id'));
        NectaResult::where('necta_result_detail_id',$request->get('detail_id'))->delete();
        //$detail->results->delete();
        $detail->delete();
        return redirect()->back()->with('error','NECTA results names do not match your application names');
    }
}
