<?php

namespace App\Http\Livewire\Finance;

use App\Domain\Academic\Models\StudyAcademicYear;
use App\Domain\Finance\Exports\PaymentsReportExport;
use App\Domain\Finance\Models\FeeType;
use App\Domain\Finance\Models\GatewayPayment;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Query\JoinClause;

class Payments extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $searchInput;

    public $feeTypeId;

    public $studyAcademicYear;

    public $from;

    public $to;

    public function getFeeTypesProperty()
    {
        return FeeType::query()->orderBy('name')->get();
    }

    public function getStudyAcademicYearsProperty()
    {
        return StudyAcademicYear::query()
            ->with('academicYear')
            ->get()
            ->sortBy('academicYear.year');
    }

    public function fromDate(): string
    {
        $fromDate = filled($this->from) ? $this->from : now()->format('Y-m-d');

        return $fromDate.' 00:00:00';
    }

    public function toDate(): string
    {
        $toDate = filled($this->to) ? $this->to : now()->format('Y-m-d');

        return $toDate.' 23:59:59';
    }

    public function exportPayments(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new PaymentsReportExport($this->gatewayPayment()),
            'payment-report.csv'
        );
    }

    public function gatewayPayment()
    {
        return GatewayPayment::query()
            ->when(filled($this->searchInput), function ($query) {
                $query->where(function ($q){
                    $q->orWhere('gateway_payments.control_no', 'like', "%$this->searchInput%")->orWhere('bill_id', 'like', "%$this->searchInput%");
                });
            })
            ->whereHas('invoice', function ($q){
                $q->where('payable_id', '>', 0)->where('payable_type', 'student')
                ->when(filled($this->feeTypeId), fn($q) => $q->where('fee_type_id', $this->feeTypeId))
                    ->when(filled($this->studyAcademicYear), fn($q) => $q->where(
                        fn($q) => $q->where('applicable_id', $this->studyAcademicYear)->where('applicable_type', 'academic_year')
                    ));
            })
            // ->join('applicants', function(JoinClause $q){
            //     $q->on('invoice.payable.applicant_id', '=', 'applicants.id')->where('campus_id', 1);
            // })
            ->join('students', function(JoinClause $q){
                $q->on('invoices.payable_id', '=', 'students.id');
            })            
            ->join('campus_program', function(JoinClause $q) {
                $q->on('students.campus_program_id', '=', 'campus_program.id')->where('campus_id', 1);
            })
            ->when(filled($this->from), fn($q) => $q->whereBetween('gateway_payments.created_at', [$this->fromDate(), $this->toDate()]))

            ->with(['invoice.payable', 'invoice.feeType',]);
    }

    public function render()
    {
        return view('livewire.finance.payments', [
            'payments' => $this->gatewayPayment()->paginate(50)
        ]);
    }
}
