<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NacteResult;
use App\Domain\Application\Models\Applicant;
use Illuminate\Support\Facades\Http;

class NacteResultController extends Controller
{
    /**
     *  Confirm NECTA results
     */
    public function confirm(Request $request)
    {
        $detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
        $applicant  = Applicant::find($request->get('applicant_id'));
        if(strtoupper($applicant->first_name) != strtoupper($detail->firstname) || strtoupper($applicant->surname) != strtoupper($detail->surname)){
            return redirect()->to('application/nullify-nacte-results?detail_id='.$request->get('nacte_result_detail_id'));
        }
        $applicant->first_name = $detail->firstname;
        $applicant->middle_name =  $detail->middlename;
        $applicant->surname = $detail->surname;
        $applicant->save();

        return redirect()->back()->with('message','NACTE results confirmed successfully');
    }

    /**
     * Delete NACTE results
     */
    public function destroy(Request $request)
    {
    	$detail = NacteResultDetail::find($request->get('nacte_result_detail_id'));
    	NacteResult::where('nacte_result_detail_id',$request->get('nacte_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
	    return redirect()->back()->with('message','NACTE results declined successfully');
    }

    /**
     * Delete NACTE results
     */
    public function nullify(Request $request)
    {
        $detail = NacteResultDetail::find($request->get('detail_id'));
        NacteResult::where('nacte_result_detail_id',$request->get('nacte_result_detail_id'))->delete();
        // $detail->results->delete();
        $detail->delete();
        return redirect()->back()->with('message','NACTE results names do not match your application names');
    }
}
