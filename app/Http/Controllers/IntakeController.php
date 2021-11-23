<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Intake;
use App\Domain\Settings\Actions\IntakeAction;
use App\Utils\Util;
use Validator;

class IntakeController extends Controller
{
    /**
     * Display a list of intakes
     */
    public function index()
    {
    	$data = [
           'intakes'=>Intake::paginate(20)
    	];
    	return view('dashboard.settings.intakes',$data)->withTitle('Intakes');
    }

    /**
     * Store intake into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:intakes',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new IntakeAction)->store($request);

        return Util::requestResponse($request,'Intake created successfully');
    }

    /**
     * Update specified intake
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


        (new IntakeAction)->update($request);

        return Util::requestResponse($request,'Intake updated successfully');
    }

    /**
     * Remove the specified intake
     */
    public function destroy(Request $request, $id)
    {
        try{
            $intake = Intake::findOrFail($id);
            $intake->delete();
            return redirect()->back()->with('message','Intake deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
