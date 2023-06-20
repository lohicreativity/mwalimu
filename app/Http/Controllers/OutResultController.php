<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\OutResultDetail;
use App\Domain\Application\Models\OutResult;
use App\Domain\Application\Models\Applicant;
use Illuminate\Support\Facades\Http;
use App\Domain\Application\Models\ApplicantProgramSelection;

class OutResultController extends Controller
{
    /**
     *  Confirm NECTA results
     */
    public function confirm(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $detail = OutResultDetail::find($request->get('out_result_detail_id'));
        $detail->verified = 1;
        $detail->save();
        $applicant  = Applicant::find($request->get('applicant_id'));
        if(strtoupper($applicant->index_number) != strtoupper($detail->index_number)){
            return redirect()->to('application/nullify-out-results?detail_id='.$request->get('out_result_detail_id'));
        }
        $applicant->results_complete_status = 1;
        $applicant->save();

        return redirect()->back()->with('message','OUT results confirmed successfully');
    }

    /**
     * Delete Out results
     */
    public function destroy(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

    	$detail = OutResultDetail::find($request->get('out_result_detail_id'));
        if($detail->verified != 1){
    	OutResult::where('out_result_detail_id',$request->get('out_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
        }
	    return redirect()->back()->with('message','OUT results declined successfully');
    }

    /**
     * Delete Out results
     */
    public function nullify(Request $request)
    {
        if(ApplicantProgramSelection::where('applicant_id',$request->get('applicant_id'))->count() != 0){
            return redirect()->back()->with('error','The action cannot be performed at the moment'); 
        }

        $detail = OutResultDetail::find($request->get('detail_id'));
        OutResult::where('out_result_detail_id',$request->get('detail_id'))->delete();
        // $detail->results->delete();
        $detail->delete();
        return redirect()->back()->with('error','OUT results index number does not match your Form IV index number');
    }

}
