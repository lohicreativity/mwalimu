<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Settings\Models\Currency;
use App\Domain\Settings\Actions\CurrencyAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class CurrencyController extends Controller
{
    /**
     * Display a list of Currencys
     */
    public function index()
    {
    	$data = [
           'currencies'=>Currency::paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.settings.currencies',$data)->withTitle('Currencies');
    }

    /**
     * Store Currency into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:currencies',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CurrencyAction)->store($request);

        return Util::requestResponse($request,'Currency created successfully');
    }

    /**
     * Update specified Currency
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return Response::json(array('error_messages'=>$validation->messages()));
           }else{
              return Redirect::back()->withInput()->withErrors($validation->messages());
           }
        }


        (new CurrencyAction)->update($request);

        return Util::requestResponse($request,'Currency updated successfully');
    }

    /**
     * Remove the specified Currency
     */
    public function destroy($id)
    {
        try{
            $currency = Currency::findOrFail($id);
            $currency->delete();
            return redirect()->back()->with('message','Currency deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
