<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Actions\FeeAmountAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class FeeAmountController extends Controller
{
    /**
     * Display a list of amounts
     */
    public function index()
    {
    	$data = [
           'amounts'=>FeeAmount::paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.finance.fee-amounts',$data)->withTitle('Fee Amounts');
    }

    /**
     * Store amount into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount_in_tzs'=>'required',
            'amount_in_usd'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeAmountAction)->store($request);

        return Util::requestResponse($request,'Fee amount created successfully');
    }

    /**
     * Update specified amount
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount_in_tzs'=>'required',
            'amount_in_usd'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeAmountAction)->update($request);

        return Util::requestResponse($request,'Fee amount updated successfully');
    }

    /**
     * Remove the specified amount
     */
    public function destroy(Request $request, $id)
    {
        try{
            $amount = FeeAmount::findOrFail($id);
            $amount->delete();
            return redirect()->back()->with('message','Fee amount deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
