<?php

namespace App\Domain\Finance\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentsReportExport implements FromQuery, WithHeadings
{
    public $payment;

    public function __construct(Builder $payment)
    {
        $this->payment = $payment;
    }

    public function query()
    {
        return $this->payment->join('invoices', 'gateway_payments.bill_id', '=', 'invoices.reference_no')
            ->join('students', function(JoinClause $q){
                $q->on('invoices.payable_id', '=', 'students.id')->where('payable_type', 'student');
            })
            ->select('students.registration_number', 'invoices.reference_no', 'invoices.control_no as control_number', 'paid_amount');
    }

    public function headings(): array
    {
        return ['registration_number', 'reference_no', 'control_number', 'paid_amount'];
    }

}
