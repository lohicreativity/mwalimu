<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Academic\Models\Award;
use App\Domain\Academic\Models\Program;
use App\Domain\Settings\Models\Level;
use App\Domain\Academic\Actions\AwardAction;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Services\ACPACService;
use App\Domain\Application\Models\Applicant;
use DB;
use App\Models\User;
use App\Utils\Util;
use Validator, Auth;

class AwardController extends Controller
{
    /**
     * Display a list of awards
     */
    public function index()
    {
      $gatepays = GatewayPayment::where('is_updated',0)->get();
      $invoice = Invoice::with('feeType')->where('control_no',$gatepays[0]->control_no)->first();
      $acpac = new ACPACService;
      if($invoice->payable_type == 'applicant'){
          $applicant = Applicant::find($invoice->payable_id);
          $stud_name = $applicant->surname.', '.$applicant->first_name.' '.$applicant->middle_name;
          $stud_reg = 'NULL';
          if(str_contains($invoice->feeType->name,'Application Fee')){

             //$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);
             $inv =  DB::table('invoices')->select(DB::raw('invoices.*,gateway_payments.*,fee_types.*'))
                       ->join('gateway_payments','invoices.control_no','=','gateway_payments.control_no')
                       ->join('fee_types','invoices.fee_type_id','=','fee_types.id')
                       ->where('invoices.id',$invoice->id)
                       ->first();
              
              if($inv->psp_name == 'National Microfinance Bank'){
                  $bank_code = 619;
                  $bank_name = 'NMB';
              }else{
                  $bank_code = 615;
                  $bank_name = 'CRDB';
              }

              dd($acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."','2','".$inv->fee_types->gl_code."')"));
             
          }
      }

    	$data = [
           'awards'=>Award::with('level')->paginate(20),
           'levels'=>Level::all(),
           'staff'=>User::find(Auth::user()->id)->staff
    	];
    	return view('dashboard.academic.awards',$data)->withTitle('Awards');
    }

    /**
     * Store award into database
     */
    public function store(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required|unique:awards',
            'code'=>'required|unique:awards'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new AwardAction)->store($request);

        return Util::requestResponse($request,'Award created successfully');
    }

    /**
     * Update specified award
     */
    public function update(Request $request)
    {
    	$validation = Validator::make($request->all(),[
            'name'=>'required',
            'code'=>'required'
        ]);

        if($validation->fails()){
           if($request->ajax()){
              return response()->json(array('error_messages'=>$validation->messages()));
           }else{
              return redirect()->back()->withInput()->withErrors($validation->messages());
           }
        }


        (new AwardAction)->update($request);

        return Util::requestResponse($request,'Award updated successfully');
    }

    /**
     * Remove the specified award
     */
    public function destroy(Request $request, $id)
    {
        try{
            $award = Award::findOrFail($id);
            if(Program::where('award_id',$award->id)->count() != 0){
               return redirect()->back()->with('message','Award cannot be deleted because it has programs');
            }else{
               $award->forceDelete();
               return redirect()->back()->with('message','Award deleted successfully');
            }
        }catch(Exception $e){
            return redirect()->back()->with('error','Unable to get the resource specified in this request');
        }
    }

    /**
     * Get by ID
     */
    public function getById(Request $request)
    {
        $award = Award::find($request->get('id'));
        return response()->json(['award'=>$award]);
    }
}
