<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Actions\FeeItemAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class FeeItemController extends Controller
{
    /**
     * Display a list of items
     */
    public function index()
    {
    	$data = [
           'items'=>FeeItem::paginate(20),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.finance.fee-items',$data)->withTitle('Fee Items');
    }

    /**
     * Store item into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'description'=>'required',
            'payment_order'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeItemAction)->store($request);

        return Util::requestResponse($request,'Fee item created successfully');
    }

    /**
     * Update specified item
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'description'=>'required',
            'payment_order'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new FeeItemAction)->update($request);

        return Util::requestResponse($request,'Fee item updated successfully');
    }

    /**
     * Remove the specified item
     */
    public function destroy(Request $request, $id)
    {
        try{
            $item = FeeItem::findOrFail($id);
            $item->delete();
            return redirect()->back()->with('message','Fee item deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
