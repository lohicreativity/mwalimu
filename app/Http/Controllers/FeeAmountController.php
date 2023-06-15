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
use App\Domain\Settings\Models\Campus;
use App\Domain\Finance\Models\Invoice;

class FeeAmountController extends Controller
{
    /**
     * Display a list of amounts
     */
    public function index(Request $request)       
    {   
        $staff = User::find(Auth::user()->id)->staff;
        if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')){
            $fee_items = FeeItem::all();   
        }else{
            $fee_items = FeeItem::where('campus_id',$staff->campus_id)->get();
        }

        $amounts = FeeAmount::with(['feeItem','campus'])->where('campus_id',$staff->campus_id)
        ->where('study_academic_year_id',$request->study_academic_year_id)->get();

        if(!Auth::user()->hasRole('administrator') && !Auth::user()->hasRole('arc') && !Auth::user()->hasRole('finance-officer') && count($amounts) == 0){
            return redirect()->back()->with('error','No fee amount specified in this academic year');    
        }

    	$data = [
           'amounts'=> !empty($request->study_academic_year_id)? $amounts : [],
           'fee_items'=>$fee_items,
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->latest()->get(),
           'staff'=>$staff,
           'previous_yr'=>FeeAmount::distinct('study_academic_year_id')->count(),
           'campuses'=>Campus::all()
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

        if(FeeAmount::where('fee_item_id',$request->get('fee_item_id'))->where('campus_id',$request->get('campus_id'))
                    ->where('study_academic_year_id',$request->get('study_academic_year_id'))->count() != 0){
            return redirect()->back()->with('error','Amount for the fee item already exists');
        }

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
        $amount = FeeAmount::findOrFail($request->get('fee_amount_id'));
        $staff = User::find(Auth::user()->id)->staff;

        if(Invoice::whereHas('feeType.feeItems.feeAmounts', function($query) use($staff, $request){$query->where('id',$request->get('fee_amount_id'))
            ->where('campus_id',$staff->campus_id)->where('study_academic_year_id',$request->get('study_academic_year_id'));})->where('applicable_id', $request->get('study_academic_year_id'))->count() != 0){ 
            return redirect()->back()->with('error','Fee amount cannot be edited. It has already been used');     
            
        }
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
             $amount->campus_id = $amt->campus_id;
             $amount->save();
         }
         return redirect()->back()->with('message','Fee amounts assigned as previous successfully');
    }

    /**
     * Remove the specified amount
     */
    public function destroy(Request $request, $id)
    {        try{

            $amount = FeeAmount::findOrFail($id);
            $staff = User::find(Auth::user()->id)->staff;
            $study_academic_year = StudyAcademicYear::latest()->first();

           if(Invoice::whereHas('feeType.feeItems.feeAmounts', function($query) use($amount, $staff, $study_academic_year, $id){$query->where('id',$id)
            ->where('campus_id',$staff->campus_id)->where('study_academic_year_id',$study_academic_year->id);})->where('applicable_id', $study_academic_year->id)->count() != 0){ 
                return redirect()->back()->with('error','Fee amount cannot be deleted. It has already been used');           
           }
           $amount->delete();
           return redirect()->back()->with('message','Fee amount deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
