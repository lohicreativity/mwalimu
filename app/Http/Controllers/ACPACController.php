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
        $data = [
           'invoices'=>Invoice::with(['payable','feeType'])->where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->latest()->get()
        ];
        return view('dashboard.finance.invoices',$data)->withTitle('Invoices');
    }

    /**
     * Display invoices
     **/
    public function receipts(Request $request)
    {
        $data = [
           'receipts'=>GatewayPayment::where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->latest()->get()
        ];
        return view('dashboard.finance.receipts',$data)->withTitle('Receipts');
    }
}
