<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NectaResultDetail;
use App\Domain\Application\Models\NectaResult;
use Illuminate\Support\Facades\Http;

class NectaResultController extends Controller
{
    /**
     * Store NECTA results
     */
    public function destroy(Request $request)
    {
    	$detail = NectaResultDetail::find($request->get('necta_result_detail_id'));
    	NectaResult::where('necta_result_detail_id',$request->get('necta_result_detail_id'))->delete();
    	// $detail->results->delete();
    	$detail->delete();
	    return redirect()->back()->with('message','NACTE results declined successfully');
    }
}
