<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Utils\DateMaker;

class ACPACController extends Controller
{
    /**
     * Display invoices
     **/
    public function invoices(Request $request)
    {
        if($request->get('begin_date') && $request->get('end_date')){
              $invoices = Invoice::has('payable')->with(['payable','feeType'])->where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->latest()->get();
        }else{
              $invoices = Invoice::has('payable')->with(['payable','feeType'])->latest()->get();
        }
        $data = [
           'invoices'=>$invoices
        ];
        return view('dashboard.finance.invoices',$data)->withTitle('Invoices');
    }

    /**
     * Display invoices
     **/
    public function receipts(Request $request)
    {
        if($request->get('begin_date') && $request->get('end_date')){
           $receipts = GatewayPayment::where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->latest()->get();
        }else{
           $receipts = GatewayPayment::latest()->get();
        }
        $data = [
           'receipts'=>$receipts
        ];
        return view('dashboard.finance.receipts',$data)->withTitle('Receipts');
    }
}
