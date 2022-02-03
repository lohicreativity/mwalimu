<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\ProgramFee;
use App\Domain\Finance\Models\FeeItem;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Academic\Models\Program;
use App\Domain\Academic\Models\Semester;
use App\Domain\Finance\Actions\ProgramFeeAction;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class ProgramFeeController extends Controller
{
    /**
     * Display a list of amounts
     */
    public function index()
    {
    	$data = [
           'fees'=>ProgramFee::with('program')->paginate(20),
           'programs'=>Program::all(),
           'fee_items'=>FeeItem::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'semesters'=>Semester::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.finance.program-fees',$data)->withTitle('Program Fees');
    }

    /**
     * Display fee structure
     */
    public function feeStructure(Request $request)
    {
    	   $data = [
            'fees'=>ProgramFee::with(['StudyAcademicYear.academicYear'])->paginate(20)
         ];
         return view('dashboard.finance.program-fee-structure',$data)->withTitle('Fee Structure');
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


        (new ProgramFeeAction)->store($request);

        return Util::requestResponse($request,'Program fee created successfully');
    }

    /**
     * Update specified program fee
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


        (new ProgramFeeAction)->update($request);

        return Util::requestResponse($request,'Program fee updated successfully');
    }

    /**
     * Remove the specified program fee
     */
    public function destroy(Request $request, $id)
    {
        try{
            $fee = ProgramFee::findOrFail($id);
            $fee->delete();
            return redirect()->back()->with('message','Program fee deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
