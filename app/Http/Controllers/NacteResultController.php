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
    public function confirm(Request $request);
    {
        $detail = NectaResultDetail::find($request->get('necta_result_detail_id'));
        $applicant  = Applicant::find($request->get('applicant_id'));
        $applicant->first_name = $detail->firstname;
        $applicant->middle_name =  $detail->middlename;
        $applicant->surname = $details->surname;
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
}
