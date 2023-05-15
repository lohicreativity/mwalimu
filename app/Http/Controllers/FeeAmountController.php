<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\FeeAmount;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Actions\FeeAmountAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class FeeAmountController extends Controller
{
    /**
     * Display a list of amounts
     */
    public function index(Request $request)       
    {   
    	$data = [
           'amounts'=> !empty($request->study_academic_year_id)? FeeAmount::with('feeItem')->where('study_academic_year_id',$request->study_academic_year_id)->get() : [],
           'fee_items'=>FeeItem::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'staff'=>User::find(Auth::user()->id)->staff,
           'previous_yr'=>FeeAmount::distinct('study_academic_year_id')->count()
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
     * Assign as previous
     */
    public function assignAsPrevious(Request $request)
    {
         $previous_ac_year = StudyAcademicYear::latest()->offset(2)->first();
         $study_academic_year = StudyAcademicYear::where('status','ACTIVE')->first();
         if(!$previous_ac_year){
              return redirect()->back()->with('error','No previous academic year');
         }
         $amounts = FeeAmount::where('study_academic_year_id',$previous_ac_year->id)->get();
         foreach($amounts as $amt){
             $amount = new FeeAmount;
             $amount->amount_in_tzs = $amt->amount_in_tzs;
             $amount->amount_in_usd = $amt->amount_in_usd;
             $amount->fee_item_id = $amt->fee_item_id;
             $amount->study_academic_year_id = $study_academic_year->id;
             $amount->save();
         }
         return redirect()->back()->with('message','Fee amounts assigned as previous successfully');
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
