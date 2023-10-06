<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Application\Models\Applicant;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Finance\Models\GatewayPayment;
use App\Domain\Registration\Models\Student;
use App\Services\ACPACService;
use DB;

class UpdateGatewayPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:update-gateway-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        $gatepays = GatewayPayment::where('is_updated',0)->get();
        foreach($gatepays as $gatepay){
            $invoice = Invoice::with('feeType')->where('control_no',$gatepay->control_no)->first();

            if(!is_null($invoice)){
                if($invoice->payable_id!=0){
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

                            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."','2','".$inv->fee_types->gl_code."')");

                        }

                    }

                    if($invoice->payable_type == 'student'){


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

                            $acpac->query("INSERT INTO invoices (INVNUMBER,INVDATE,INVDESC,IDCUST,NAMECUST,[LINENO],REVACT,REVDESC,REVREF,REVAMT,IMPORTED,IMPDATE) VALUES ('".$inv->control_no."','".date('Y',strtotime($inv->created_at))."','".$inv->description."','".$stud_reg."','".$stud_name."','1','".$inv->gl_code."','".$inv->name."','".$inv->description."','".$inv->amount."','0','".date('Ymd',strtotime(now()))."')");

                            if($inv->psp_name == 'National Microfinance Bank'){
                                $bank_code = 619;
                                $bank_name = 'NMB';
                            }else{
                                $bank_code = 615;
                                $bank_name = 'CRDB';
                            }

                            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");

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

                            $acpac->query("INSERT INTO receipts (BANK,BANKNAME,RCPNUMBER,RCPDATE,RCPDESC,IDCUST,NAMECUST,INVOICE,AMTAPPLIED,IMPORTED,IMPDATE,RCPTYPE,REVACT) VALUES ('".$bank_code."','".$bank_name."','".substr($inv->transaction_id,5)."','".date('Ymd',strtotime($inv->datetime))."','".$inv->description."','".$stud_reg."','".$stud_name."','".$inv->control_no."','".$inv->paid_amount."','0','".date('Ymd',strtotime(now()))."','1','')");
                        }
                    }
                    GatewayPayment::where('id',$gatepay->id)->update(['is_updated'=>1]);

                }//end payable_id is not equal to zero
            }//end invoice is not null

        }
        DB::commit();
        return 0;
    }
}
