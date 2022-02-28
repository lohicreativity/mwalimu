<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Application\Models\NacteResultDetail;
use App\Domain\Application\Models\NacteResult;
use Illuminate\Support\Facades\Http;

class NacteResultController extends Controller
{
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
