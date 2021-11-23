<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Level;
use App\Domain\Settings\Actions\LevelAction;
use App\Utils\Util;
use Validator;

class LevelController extends Controller
{
    /**
     * Display a list of levels
     */
    public function index()
    {
    	$data = [
           'levels'=>Level::paginate(20)
    	];
    	return view('dashboard.settings.levels',$data)->withTitle('Levels');
    }

    /**
     * Store level into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:levels',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new LevelAction)->store($request);

        return Util::requestResponse($request,'Level created successfully');
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


        (new LevelAction)->update($request);

        return Util::requestResponse($request,'Level updated successfully');
    }

    /**
     * Remove the specified level
     */
    public function destroy(Request $request, $id)
    {
        try{
            $level = Level::findOrFail($id);
            $level->delete();
            return redirect()->back()->with('message','Level deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
