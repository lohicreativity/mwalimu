<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Actions\FeeItemAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;
use App\Domain\Settings\Models\Campus;

class FeeItemController extends Controller
{
    /**
     * Display a list of items
     */
    public function index()
    {
        $staff = User::find(Auth::user()->id)->staff;

        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $fee_items = FeeItem::with('campus')->get();   
        }else{
            $fee_items = FeeItem::with('campus')->where('campus_id',$staff->campus_id)->get();
        }

    	$data = [
           'items'=>$fee_items,
           'fee_types'=>FeeType::all(),
           'staff'=>$staff,
           'campuses'=>Campus::all()
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

        // if(FeeItem::where('fee_type_id',$request->get('fee_type_id'))->count() != 0){
        //     return redirect()->back()->with('error','Fee item with this fee type already exists');
        // }

        if(Feeitem::where('name',$request->get('name'))->where('campus_id',$request->get('campus_id'))->count() != 0){
            return redirect()->back()->with('error','Fee item with this name already exists');
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

        // if(FeeItem::where('name',$request->get('name'))->count() != 0){
        //     return redirect()->back()->with('error','Fee item with this name already exists');
        // }

        (new FeeItemAction)->update($request);

        return Util::requestResponse($request,'Fee item updated successfully');
    }

    /**
     * Remove the specified item
     */
    public function destroy(Request $request, $id)
    {
        try{
            $item = FeeItem::with('feeAmounts')->findOrFail($id);
            if(count($item->feeAmounts) != 0){
                return redirect()->back()->with('error','Fee item has amounts and cannot be deleted');
            }
            $item->delete();
            return redirect()->back()->with('message','Fee item deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
