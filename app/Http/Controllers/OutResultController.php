<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\OutResultDetail;
use App\Domain\Application\Models\OutResult;
use App\Domain\Application\Models\Applicant;
use Illuminate\Support\Facades\Http;

class OutResultController extends Controller
{
    /**
     *  Confirm NECTA results
     */
    public function confirm(Request $request)
    {
        $detail = OutResultDetail::find($request->get('out_result_detail_id'));
        $applicant  = Applicant::find($request->get('applicant_id'));
        if(strtoupper($applicant->first_name) != strtoupper($detail->firstname) || strtoupper($applicant->surname) != strtoupper($detail->surname)){
            return redirect()->to('application/nullify-out-results?detail_id='.$request->get('out_result_detail_id'));
        }
        $applicant->first_name = $detail->firstname;
        $applicant->middle_name =  $detail->middlename;
        $applicant->surname = $detail->surname;
        $applicant->save();

        return redirect()->back()->with('message','Out results confirmed successfully');
    }

    /**
     * Delete Out results
     */
    public function destroy(Request $request)
    {
    	$detail = OutResultDetail::find($request->get('out_result_detail_id'));
    	OutResult::where('out_result_detail_id',$request->get('out_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
	    return redirect()->back()->with('message','Out results declined successfully');
    }

    /**
     * Delete Out results
     */
    public function nullify(Request $request)
    {
        $detail = OutResultDetail::find($request->get('detail_id'));
        OutResult::where('out_result_detail_id',$request->get('out_result_detail_id'))->delete();
        // $detail->results->delete();
        $detail->delete();
        return redirect()->back()->with('message','Out results names do not match your application names');
    }

}
