<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Settings\Models\Region;
use App\Domain\Settings\Models\District;
use App\Domain\Settings\Models\Ward;

class HomeController extends Controller
{
    /**
     * Return a list of regions given country id
     */
    public function getRegions(Request $request)
    {
    	$regions = Region::where('country_id',$request->get('country_id'))->get();
    	if(count($regions) != 0){
            return response()->json(['status'=>'success','regions'=>$regions]);
    	}else{
    		return response()->json(['status'=>'failed','regions'=>$regions]);
    	}
    }

    /**
     * Return a list of regions given country id
     */
    public function getDistricts(Request $request)
    {
    	$districts = District::where('region_id',$request->get('region_id'))->get();
    	if(count($districts) != 0){
            return response()->json(['status'=>'success','districts'=>$districts]);
    	}else{
    		return response()->json(['status'=>'failed','districts'=>$districts],404);
    	}
    }

    /**
     * Return a list of wards given district id
     */
    public function getWards(Request $request)
    {
    	$wards = Ward::where('district_id',$request->get('district_id'))->get();
    	if(count($wards) != 0){
            return response()->json(['status'=>'success','wards'=>$wards]);
    	}else{
    		return response()->json(['status'=>'failed','wards'=>$wards]);
    	}
    }
}
