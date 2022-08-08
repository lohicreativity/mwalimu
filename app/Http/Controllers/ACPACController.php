<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Utils\DateMaker;

class ACPACController extends Controller
{
    /**
     * Display invoices
     **/
    public function invoices(Request $request)
    {
        if($request->get('begin_date') && $request->get('end_date')){
              $invoices = Invoice::has('payable')->whereHas('payable.campusProgram',function($query) use($request){
                    $query->where('campus_id',$request->get('campus_id'));
              })->with(['payable.campusProgram.program','feeType'])->where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->where('payable_type','student')->where('payable_type','student')->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
        }else{
              $invoices = Invoice::has('payable')->whereHas('payable.campusProgram',function($query) use($request){
                    $query->where('campus_id',$request->get('campus_id'));
              })->with(['payable.campusProgram.program','feeType'])->where('payable_type','student')->where('payable_type','student')->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
        }
        $data = [
           'invoices'=>$invoices,
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'request'=>$request
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
