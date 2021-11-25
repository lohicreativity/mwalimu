<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Postponement;
use App\Domain\Academic\Actions\PostponementAction;
use App\Utils\Util;
use Validator;


class PostponementController extends Controller
{
     /**
     * Display a list of postponements
     */
    public function index()
    {
    	$data = [
           'postponements'=>Postponement::paginate(20)
    	];
    	return view('dashboard.academic.postponements',$data)->withTitle('Postponements');
    }

    /**
     * Store postponement into database
     */
    public function store(Request $request)
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


        (new postponementAction)->store($request);

        return Util::requestResponse($request,'postponement created successfully');
    }

    /**
     * Update specified postponement
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


        (new postponementAction)->update($request);

        return Util::requestResponse($request,'postponement updated successfully');
    }

    /**
     * Remove the specified postponement
     */
    public function destroy(Request $request, $id)
    {
        try{
            $postponement = postponement::findOrFail($id);
            if(Award::where('award_id',$postponement->id)->count() != 0){
               return redirect()->back()->with('message','postponement cannot be deleted because it has awards');
            }else{
               $postponement->delete();
               return redirect()->back()->with('message','postponement deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
