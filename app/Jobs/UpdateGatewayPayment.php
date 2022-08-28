<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Domain\Application\Models\Applicant;
use App\Domain\Academic\Models\Appeal;
use App\Domain\Academic\Models\PerfomanceReportRequest;
use App\Domain\Academic\Models\TranscriptRequest;
use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Finance\Models\LoanAllocation;
use App\Domain\Finance\Models\PaymentReconciliation;
use App\Domain\Registration\Models\Student;
use Illuminate\Support\Facades\Log;
use App\Services\ACPACService;
use DB;

class UpdateGatewayPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gatepay;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GatewayPayment $gatepay)
    {
        $this->gatepay = $gatepay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        $invoice = Invoice::with('feeType')->where('control_no',$this->gatepay->control_no)->first();
        $acpac = new ACPACService;
        if($invoice->payable_type == 'applicant'){
            $applicant = Applicant::find($invoice->payable_id);
            $stud_name = $applicant->surname.', '.$applicant->first_name.' '.$applicant->middle_name;
            $stud_reg = 'NULL';
            if(str_contains($invoice->feeType->name,'Application Fee')){
               $applicant->payment_complete_status = 1;
               $applicant->save();

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

                $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");
               
            }

            

            if(str_contains($invoice->feeType->name,'Tuition Fee')){
                $paid_amount = GatewayPayment::where('bill_id',$invoice->reference_no)->sum('paid_amount');
                $percentage = $paid_amount/$invoice->amount;
                $applicant = Applicant::with('applicationWindow')->find($invoice->payable_id);

                $ac_year = date('Y',strtotime($applicant->applicationWindow->end_date));
                $study_academic_year = StudyAcademicYear::whereHas('academicYear',function($query) use($ac_year){
                       $query->where('year','LIKE','%'.$ac_year.'/%');
                })->first();

                if($study_academic_year){
                    $loan_allocation = LoanAllocation::where('index_number',$applicant->index_number)->where('study_academic_year_id',$study_academic_year->id)->first();
                }else{
                    $loan_allocation = null;
                }           

                if($loan_allocation){
                   $percentage = ($paid_amount+$loan_allocation->tuition_fee)/$invoice->amount;
                   $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
                }else{
                   $applicant->tuition_payment_check = $percentage >= 0.6? 1 : 0;
                }
                $applicant->save();
            }

            if(str_contains($invoice->feeType->name,'Miscellaneous')){
                $applicant = Applicant::find($invoice->payable_id);
                $applicant->other_payment_check = $data['paid_amount'] == $invoice->amount? 1 : 0;
                $applicant->save();
            }
            
        }

        if($invoice->payable_type == 'student'){
            if(str_contains($invoice->feeType->name,'Appeal')){
                 Appeal::where('student_id',$invoice->payable_id)->where('invoice_id',$invoice->id)->update(['is_paid'=>1]);
            }

            if(str_contains($invoice->feeType->name,'Performance Report')){
                 PerfomanceReportRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID','status'=>'PENDING']);
            }

            if(str_contains($invoice->feeType->name,'Transcript')){
                 TranscriptRequest::where('student_id',$invoice->payable_id)->update(['payment_status'=>'PAID']);
            }

            $student = Student::find($invoice->payable_id);
            
            $stud_name = $student->surname.', '.$student->first_name.' '.$student->middle_name;
            $stud_reg = substr($student->registration_number, 5);
            $stud_reg = str_replace('/', '', $stud_reg);
            $parts = explode('.', $stud_reg);
            if($parts[0] == 'BTC'){
                $stud_reg = 'BT'.$parts[1];
            }else{
                $stud_reg = $parts[0].$parts[1];
            }

            if($student->registration_year >= 2022){
                //$inv = Invoice::with(['gatewayPayment','feeType'])->find($invoice->id);
                $inv =  DB::table('invoices')->select(DB::raw('invoices.*,gateway_payments.*,fee_types.*'))
                         ->join('gateway_payments','invoices.control_no','=','gateway_payments.control_no')
                         ->join('fee_types','invoices.fee_type_id','=','fee_types.id')
                         ->where('invoices.id',$invoice->id)
                         ->first();

                $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$inv->control_no."','".date('Y',strtotime($inv->created_at))."','".$inv->description."','".$stud_reg."','".$stud_name."','1','".$inv->gl_code."','".$inv->name."','".$inv->description."','".$inv->amount."','0','".date('Y',strtotime(now()))."')");

                if($inv->psp_name == 'National Microfinance Bank'){
                    $bank_code = 619;
                    $bank_name = 'NMB';
                }else{
                    $bank_code = 615;
                    $bank_name = 'CRDB';
                }

                $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");

            }else{
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

                $stud_reg = 'NULL';

                $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."')");
            }
        }
        GatewayPayment::where('id',$this->gatepay->id)->update(['is_updated'=>1]);
        DB::commit();
    }
}
