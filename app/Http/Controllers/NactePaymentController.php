<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Actions\NactePaymentAction;
use App\Domain\Finance\Models\NactePayment;
use App\Domain\Settings\Models\Campus;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Utils\Util;
use Validator;

class NactePaymentController extends Controller
{
    /**
     * Show NACTE payments
     */
    public function index(Request $request)
    {
         return 'Nacte payments';
         $data = [
            'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
            'campuses'=>Campus::all(),
            'payments'=>NactePayment::with(['campus','studyAcademicYear.academicYear'])->paginate(20),
            'request'=>$request
         ];
         return view('dashboard.finance.nacte-payments',$data)->withTitle('NACTE Payments');
    }

    /**
     * Store payment
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount'=>'required',
            'reference_number'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new NactePaymentAction)->store($request);

        return Util::requestResponse($request,'NACTE payment reference created successfully');
    }

    /**
     * Update payment
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'amount'=>'required',
            'reference_number'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new NactePaymentAction)->update($request);

        return Util::requestResponse($request,'NACTE payment reference updated successfully');
    }

    /**
     * Delete payment
     */
    public function destroy($id)
    {
        try{
            $payment = NactePayment::findOrFail($id);
            $payment->delete();
            return redirect()->back()->with('message','NACTE payment reference deleted successfully');
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }
}
