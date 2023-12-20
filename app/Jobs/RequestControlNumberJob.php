<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\Invoice;
use App\Domain\Registration\Models\Student;

class RequestControlNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        set_time_limit(3600);

        $this->handle();
    }

    public function handle(): void
    {
        $this->getInvoicesWithNoControlNumber()
            ->chunk(10, function (Collection $invoices){
                $invoices->each(function (Invoice $invoice){
                    $feeType = FeeType::firstWhere('id', $invoice->fee_type_id);

                    $student = null;
                    if($invoice->payable_type == 'student'){
                        $student = Student::firstWhere('id',$invoice->payable_id);
                    }

                    $email = $student->email? $student->email : 'admission@mnma.ac.tz';
                    $first_name = str_contains($student->first_name,"'")? str_replace("'","",$student->first_name) : $student->first_name;
                    $surname = str_contains($student->surname,"'")? str_replace("'","",$student->surname) : $student->surname;
        
                    $number_filter = preg_replace('/[^0-9]/','',$email);
                    $payer_email = empty($number_filter)? $email : 'admission@mnma.ac.tz';
                    $data = [
                        'payment_ref'=>$invoice->reference_no,
                        'sub_sp_code'=>config('constants.SUBSPCODE'),
                        'amount'=> $invoice->amount,
                        'desc'=> $feeType->description,
                        'gfs_code'=> $feeType->gfs_code,
                        'payment_type'=> $feeType->payment_option,
                        'payerid'=> $invoice->payable_id,
                        'payer_name'=> $first_name.' '.$surname,
                        'payer_cell'=> $student->phone,
                        'payer_email'=> $payer_email,
                        'days_expires_after'=> $feeType->duration,
                        'generated_by'=>'SP',
                        'approved_by'=>'SP',
                        'currency'=>$invoice->currency
                    ];
                    
                    $this->requestControlNumber($data);
                    
                });
            });
    }

    public function requestControlNumber(array $data): void
    {
        Http::withHeaders(['X-CSRF-TOKEN' => csrf_token()])
            ->post(url('bills/post_bill'), $data);
    }

    public function getInvoicesWithNoControlNumber()
    {
        return Invoice::whereNull('control_no')
                      ->where('payable_id', '>', 0)
                      ->where('payable_type','student');
    }
}