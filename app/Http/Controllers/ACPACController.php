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
     * Download invoicesd
     **/
    public function downloadInvoices(Request $request)
    {
         $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=INVOICES-.'.date('Y-m-d').'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

        if($request->get('begin_date') && $request->get('end_date')){
              $invoices = Invoice::has('payable')->where('payable_type','student')->with(['payable.campusProgram.program','feeType'])->where('created_at','>=',DateMaker::toDBDate($request->get('begin_date')))->where('created_at','<=',DateMaker::toDBDate($request->get('end_date')))->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
        }else{
              $invoices = Invoice::has('payable')->where('payable_type','student')->with(['payable.campusProgram.program','feeType'])->where('applicable_id',$request->get('study_academic_year_id'))->where('applicable_type','academic_year')->latest()->get();
        }
       $callback = function() use ($invoices) 
          {
              $file_handle = fopen('php://output', 'w');
              fputcsv($file_handle,['REFERENCE NUMBER','CUSTOMER ID','PAYER NAME','PROGRAMME','YEAR OF STUDY','BILL TYPE','BILL AMOUNT','CURRENCY','CONTROL NUMBER','DATE CREATED']);
              foreach ($invoices as $invoice) { 

                  if($invoice->payable_type == 'student'){
                  $stud_reg = substr($invoice->payable->registration_number, 5);
                  $stud_reg = str_replace('/', '', $stud_reg);
                  $parts = explode('.', $stud_reg);
                  if($parts[0] == 'BTC'){
                      $stud_reg = 'BT'.$parts[1];
                  }else{
                      $stud_reg = $parts[0].$parts[1];
                  }
                  }else{
                      $stud_reg = null;
                  }

                  fputcsv($file_handle, [$invoice->reference_no,$stud_reg,$invoice->payable->first_name.' '.$invoice->payable->middle_name.' '.$invoice->payable->surname,$invoice->payable->campusProgram->code,$invoice->payable->year_of_study,$invoice->feeType->name,number_format($invoice->amount,2),$invoice->currency, $invoice->control_no,$invoice->created_at
                    ]);
              }
              fclose($file_handle);
          };

          return response()->stream($callback, 200, $headers);
    }

    /**
     * Display invoices
     **/
    public function receipts(Request $request)
    {
        if($request->get('begin_date') && $request->get('end_date')){
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.code as programme'))
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
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.code as programme'))
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

    /**
     * Display invoices
     **/
    public function downloadReceipts(Request $request)
    {
         $headers = [
                      'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                      'Content-type'        => 'text/csv',
                      'Content-Disposition' => 'attachment; filename=RECEIPTS-.'.date('Y-m-d').'.csv',
                      'Expires'             => '0',
                      'Pragma'              => 'public'
              ];

        if($request->get('begin_date') && $request->get('end_date')){
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.code as programme'))
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
           $receipts = DB::table('gateway_payments')->select(DB::raw('gateway_payments.*, invoices.*, students.*, programs.code as programme'))
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

        $callback = function() use ($receipts) 
          {
              $file_handle = fopen('php://output', 'w');
              fputcsv($file_handle,['RECEIPT NUMBER','CUSTOMER ID','PAYER NAME','PROGRAMME','YEAR OF STUDY','BILL AMOUNT','PAID AMOUNT','CURRENCY','CONTROL NUMBER','DATE CREATED']);
              foreach ($receipts as $receipt) { 

                  if($receipt->payable_type == 'student'){
                  $stud_reg = substr($receipt->registration_number, 5);
                  $stud_reg = str_replace('/', '', $stud_reg);
                  $parts = explode('.', $stud_reg);
                  if($parts[0] == 'BTC'){
                      $stud_reg = 'BT'.$parts[1];
                  }else{
                      $stud_reg = $parts[0].$parts[1];
                  }
                  }else{
                      $stud_reg = null;
                  }

                  fputcsv($file_handle, [$receipt->transaction_id,$stud_reg,$receipt->payer_name,$receipt->programme,$receipt->year_of_study,number_format($receipt->amount,2),number_format($receipt->paid_amount,2),$receipt->currency, $receipt->control_no,$receipt->created_at
                    ]);
              }
              fclose($file_handle);
          };

          return response()->stream($callback, 200, $headers);
        
    }
}
