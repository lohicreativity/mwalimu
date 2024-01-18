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
        $staff = User::find(Auth::user()->id)->staff;
        if (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc')) {
            $campus_id = [1,2,3];
        }else{
            $campus_id = [$staff->campus_id];
        }
        return $this->payment->join('invoices', 'gateway_payments.bill_id', '=', 'invoices.reference_no')
            ->join('students', function(JoinClause $q){
                $q->on('invoices.payable_id', '=', 'students.id')->where('payable_type', 'student');
            })            
            ->join('campus_program', function(JoinClause $q) use($campus_id){
                $q->on('students.campus_program_id', '=', 'campus_program.id')->whereIn('campus_id', $campus_id);
            })
            ->join('fee_types', function(JoinClause $q){
                $q->on('invoices.fee_type_id', '=', 'fee_types.id');
            })
            ->select('students.registration_number as "Registration Number"', 'students.first_name as "First Name"','students.middle_name as "Middle Name"',
                     'students.surname as Surname','students.gender as Sex','students.phone as Phone','students.year_of_study as "Year of Study"', 'campus_program.code as "Programme Code"',
                     'invoices.reference_no as "Invoice Number"', 'invoices.control_no as "Control Number"', 'fee_types.name as "Fee Type"', 'paid_amount as "Amount Paid"'
                     );
    }

    public function headings(): array
    {
        return ['Registration Number', 'First Name', 'Middle Name', 'Surname', 'Sex', 'Phone', 'Year of Study', 'Programme', 'Invoice Number', 'Control Number', 'Fee Type', 'Amount Paid'];
    }

}
