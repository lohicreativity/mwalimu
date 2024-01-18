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
            ->join('campus_program', function(JoinClause $q){
                $q->on('students.campus_program_id', '=', 'campus_program.id')->where('campus_id', 1);
            })
            ->join('fee_types', function(JoinClause $q){
                $q->on('invoices.fee_type_id', '=', 'fee_types.id');
            })
            ->select('students.registration_number as Reg#', 'students.first_name as "First Name"','students.middle_name as "Middle Name"',
                     'students.surname as Surname','students.gender as Sex','students.phone as Phone','students.year_of_study as "Year of Study"',
                     'invoices.reference_no as Invoice#', 'invoices.control_no as Control#', 'paid_amount as "Amount Paid"','campus_program.code as "Programme Code"',
                     'fee_types.name as "Fee Type"');
    }

    public function headings(): array
    {
        return ['Reg#', 'First Name', 'Middle Name', 'Surname', 'Sex', 'Phone', 'Year of Study', 'Programme Code', 'Invoice#', 'Control#', 'Fee Type', 'Amount Paid'];
    }

}
