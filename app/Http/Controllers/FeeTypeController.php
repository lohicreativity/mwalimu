<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Actions\FeeTypeAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class FeeTypeController extends Controller
{
    /**
     * Display a list of types
     */
    public function index()
    {
    	$data = [
           'types'=>FeeType::paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.finance.fee-types',$data)->withTitle('types');
    }

    /**
     * Store type into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'code'=>'required',
            'gfs_code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeTypeAction)->store($request);

        return Util::requestResponse($request,'Fee type created successfully');
    }

    /**
     * Update specified type
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'code'=>'required',
            'gfs_code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeTypeAction)->update($request);

        return Util::requestResponse($request,'Fee type updated successfully');
    }

    /**
     * Remove the specified type
     */
    public function destroy(Request $request, $id)
    {
        try{
            $type = FeeType::findOrFail($id);
            $type->delete();
            return redirect()->back()->with('message','Fee type deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
