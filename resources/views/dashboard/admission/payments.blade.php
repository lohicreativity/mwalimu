@extends('layouts.app')

@section('content')

<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{ asset('dist/img/logo.png') }}" alt="{{ Config::get('constants.SITE_NAME') }}" height="60" width="60">
  </div>

  @include('layouts.auth-header')

  @include('layouts.sidebar')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Payments - {{ $campus->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">Home</li>
              <li class="breadcrumb-item active"><a href="#">{{ __('Payments') }}</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-12">

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Payments</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <table class="table table-bordered">
                    <tr>
                       <td>Fee Item</td>
                       <td>Fee Amount</td>
                       <td>Control Number</td>
                       <td>Invoice Amount</td>
                       <td>Amount Paid</td>
                       <td>Balance</td>
                    </tr>
                    <tr>
                       <td>Programme Fee</td>
                       @if($applicant->has_postponed == 1)
                       <td> 100,000 TZS</td>
                       @else
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $program_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $program_fee->amount_in_usd }} USD</td>
                       @endif
                       @endif
                       <td>@if($program_fee_invoice) {{ $program_fee_invoice->control_no }}@endif <a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a></td>
                       <td>@if($program_fee_invoice) {{ $program_fee_invoice->amount }} {{ $program_fee_invoice->currency }} @endif</td>
                       <td>
                         @if($loan_allocation)
                         @if(isset($program_fee_invoice->gatewayPayment))
                            {{ $program_fee_invoice->gatewayPayment->paid_amount+$loan_allocation->tuition_fee }} TZS
                         @else
                            {{ $loan_allocation->tuition_fee }} TZS
                         @endif
                         @else
                         @if(isset($program_fee_invoice->gatewayPayment))
                            {{ $program_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                         @endif
                       </td>
                       <td>
                         @if($loan_allocation)
                         @if(isset($program_fee_invoice->gatewayPayment))
                            {{ $program_fee->amount_in_tzs-$program_fee_invoice->gatewayPayment->paid_amount+$loan_allocation->tuition_fee }} TZS
                         @else
                            {{ $program_fee->amount_in_tzs - $loan_allocation->tuition_fee }} TZS
                         @endif
                         @else
                         @if(isset($program_fee_invoice->gatewayPayment))
                            {{ $program_fee_invoice->gatewayPayment->bill_amount-$program_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                         @endif
                         
                       </td>
                    </tr>
                    @if($applicant->has_postponed != 1)
                    <tr>
                       <td>Other Fees</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $other_fees_tzs }} TZS</td>
                       @else
                       <td>{{ $other_fees_usd }} USD</td>
                       @endif
                       <td>@if($other_fee_invoice) {{ $other_fee_invoice->control_no }}@endif <a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a></td>
                       <td>
                         @if(isset($other_fee_invoice->gatewayPayment))
                            {{ $other_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                       </td>
                       <td>
                         @if(isset($other_fee_invoice->gatewayPayment))
                            {{ $other_fee_invoice->gatewayPayment->bill_amount-$other_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                       </td>

                    </tr>

                    @if($insurance_fee)
                    <tr>
                       <td>Insurance Fee</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $insurance_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $insurance_fee->amount_in_usd }} USD</td>
                       @endif
                       <td>@if($insurance_fee_invoice) {{ $insurance_fee_invoice->control_no }}@endif <a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a></td>
                       <td></td>
                    </tr>
                    @endif
                    @if($hostel_fee)
                    <tr>
                       <td>Hostel Fee</td>
                       @if($applicant->country->code == 'TZ')
                       <td>{{ $hostel_fee->amount_in_tzs }} TZS</td>
                       @else
                       <td>{{ $hostel_fee->amount_in_usd }} USD</td>
                       @endif
                       <td>@if($hostel_fee_invoice) {{ $hostel_fee_invoice->control_no }}@endif <a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a></td>
                       <td>
                         @if(isset($hostel_fee_invoice->gatewayPayment))
                            {{ $hostel_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                       </td>
                       <td>
                         @if(isset($hostel_fee_invoice->gatewayPayment))
                            {{ $hostel_fee_invoice->gatewayPayment->bill_amount-$hostel_fee_invoice->gatewayPayment->paid_amount }} TZS
                         @endif
                       </td>
                    </tr>
                    @endif
                    @endif
                    @if(!$program_fee_invoice)
                    <tr>
                      <td>
                        {!! Form::open(['url'=>'admission/request-control-number','class'=>'ss-form-processing']) !!}
                          {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                          <button type="submit" class="btn btn-primary">Request Control Number</button>
                        {!! Form::close() !!}
                      </td>
                    </tr>
                    @endif
                 </table>
              </div>
            </div>
            <!-- / .card -->
          </div>
        </div>
        <!-- / .row -->
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  @include('layouts.footer')

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

@endsection
