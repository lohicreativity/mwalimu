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
        $applicant  = Applicant::find($request->get('applicant_id'));
        $applicant->first_name = $detail->first_name;
        $applicant->middle_name =  $detail->middle_name;
        $applicant->surname = $details->last_name;
        $applicant->save();

        return redirect()->back()->with('message','NECTA results confirmed successfully');
    }
    /**
     * Store NECTA results
     */
    public function destroy(Request $request)
    {
    	$detail = NectaResultDetail::find($request->get('necta_result_detail_id'));
    	NectaResult::where('necta_result_detail_id',$request->get('necta_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
	    return redirect()->back()->with('message','NECTA results declined successfully');
    }
}
