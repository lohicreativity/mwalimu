<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Settings\Models\Campus;
use App\Utils\DateMaker;
use DB;

class ACPACController extends Controller
{
    /**
     * Display invoices
     **/
    public function invoices(Request $request)
    {
        if($request->get('begin_date') && $request->get('end_date')){
              $invoices = Invoice::has('payable')->where('payable_type','student')->with(['payable.campusProgram.program','feeType'])->where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
        }else{
              $invoices = Invoice::has('payable')->where('payable_type','student')->with(['payable.campusProgram.program','feeType'])->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
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
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.name as programme'))
               ->join('invoices','gateway_payments.control_no','=','invoices.control_no')
               ->join('students','invoices.payable_id','=','students.id')
               ->join('campus_program','students.campus_program_id','=','campus_program.id')
               ->join('programs','campus_program.program_id','=','programs.id')
               ->join('study_academic_years','invoices.applicable_id','=','study_academic_years.id')
               ->where('invoices.payable_type','student')
               ->where('campus_program.campus_id',$request->get('campus_id'))
               ->where('invoices.applicable_type','academic_year')
               ->where('invoices.applicable_id',$request->get('study_academic_year_id'))
               ->where('gateway_payments.created_at','>=',DateMaker::toDBDate($request->get('begin_date')))
               ->where('gateway_payments.created_at','<=',DateMaker::toDBDate($request->get('end_date')))
               ->get();
        }else{
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.name as programme'))
               ->join('invoices','gateway_payments.control_no','=','invoices.control_no')
               ->join('students','invoices.payable_id','=','students.id')
               ->join('campus_program','students.campus_program_id','=','campus_program.id')
               ->join('programs','campus_program.program_id','=','programs.id')
               ->join('study_academic_years','invoices.applicable_id','=','study_academic_years.id')
               ->where('invoices.payable_type','student')
               ->where('campus_program.campus_id',$request->get('campus_id'))
               ->where('invoices.applicable_type','academic_year')
               ->where('invoices.applicable_id',$request->get('study_academic_year_id'))
               ->get();
        }
        $data = [
           'receipts'=>$receipts,
           'campuses'=>Campus::all(),
           'study_academic_years'=>StudyAcademicYear::with('academicYear')->get(),
           'request'=>$request
        ];
        return view('dashboard.finance.receipts',$data)->withTitle('Receipts');
    }
}
