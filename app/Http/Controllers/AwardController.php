<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Level;
use App\Domain\Academic\Actions\AwardAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class AwardController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index()
    {
    	$data = [
           'awards'=>Award::with('level')->paginate(20),
           'levels'=>Level::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.awards',$data)->withTitle('Awards');
    }

    /**
     * Store award into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:awards',
            'code'=>'required|unique:awards'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new AwardAction)->store($request);

        return Util::requestResponse($request,'Award created successfully');
    }

    /**
     * Update specified award
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new AwardAction)->update($request);

        return Util::requestResponse($request,'Award updated successfully');
    }

    /**
     * Remove the specified award
     */
    public function destroy(Request $request, $id)
    {
        try{
            $award = Award::findOrFail($id);
            if(Program::where('award_id',$award->id)->count() != 0){
               return redirect()->back()->with('message','Award cannot be deleted because it has programs');
            }else{
               $award->forceDelete();
               return redirect()->back()->with('message','Award deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Get by ID
     */
    public function getById(Request $request)
    {
        $award = Award::find($request->get('id'));
        return response()->json(['award'=>$award]);
    }
}
