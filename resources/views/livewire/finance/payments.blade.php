<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('List of Payments') }}</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">

        <div class="row">
            <div class="col-2">
                <input wire:model="searchInput" type="text"  class="form-control" placeholder="Search by Inv# or control#"/>
            </div>

            <div class="form-group col-2">
                <select wire:model="feeTypeId" class="form-control">
                    <option value="">Select Fee Type</option>
                    @foreach($this->feeTypes as $feeType)
                        <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-2">
                <select wire:model="studyAcademicYear" class="form-control">
                    <option value="">Select Study Academic Year</option>
                    @foreach($this->studyAcademicYears as $studyAcademicYear)
                        <option value="{{ $studyAcademicYear->id }}" >{{ $studyAcademicYear->academicYear->year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <input wire:model="from" type="date"  class="form-control" />
                <small class="text-muted">Start date</small>
            </div>

            <div class="col-2">
                <input wire:model="to" type="date"  class="form-control" />
                <small class="text-muted">End date</small>
            </div>

            <div class="col-2">
                <button wire:click="exportPayments()" type="button" class="btn btn-primary">{{ __('Export') }}</button>
            </div>
        </div>


        <table id="example2" class="table table-bordered table-hover ss-margin-top">
            <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Payer Name</th>
                <th>Payer ID</th>
                <th>For</th>
                <th>Paid Amount</th>
                <th>Reference Number</th>
                <th>Control Number</th>
                <th>Receipt Number</th>
            </tr>
            </thead>
            <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->bill_id }}</td>
                    <td>{{ $payment->invoice?->payable?->first_name }} {{ $payment->invoice?->payable?->middle_name }} {{ $payment->invoice?->payable?->surname }}</td>
                    <td>{{ $payment->invoice->payable?->registration_number }}</td>
                    <td>{{ $payment->invoice->feeType->name }}</td>
                    <td>{{ number_format($payment->paid_amount,2) }} {{ $payment->ccy }}</td>
                    <td>{{ $payment->pay_refId }}</td>
                    <td>{{ $payment->control_no }}</td>
                    <td>{{ $payment->psp_receipt_no }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
        <div class="ss-pagination-links">
            {!! $payments->links() !!}
        </div>
    </div>
    <!-- /.card-body -->
</div>
