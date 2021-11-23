<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\NTALevel;
use App\Domain\Academic\Models\Award;
use App\Domain\Settings\Actions\NTALevelAction;
use App\Utils\Util;
use Validator;

class NTALevelController extends Controller
{
    /**
     * Display a list of levels
     */
    public function index()
    {
    	$data = [
           'nta_levels'=>NTALevel::with('award')->paginate(20),
           'awards'=>Award::all()
    	];
    	return view('dashboard.settings.nta-levels',$data)->withTitle('NTA Levels');
    }

    /**
     * Store level into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:nta_levels',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new NTALevelAction)->store($request);

        return Util::requestResponse($request,'NTA Level created successfully');
    }

    /**
     * Update specified level
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new NTALevelAction)->update($request);

        return Util::requestResponse($request,'NTA Level updated successfully');
    }

    /**
     * Remove the specified level
     */
    public function destroy(Request $request, $id)
    {
        try{
            $level = NTALevel::findOrFail($id);
            $level->delete();
            return redirect()->back()->with('message','NTA Level deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
