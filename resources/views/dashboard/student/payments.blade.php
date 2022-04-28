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
            <h1 class="m-0">Payments</h1>
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
                       <td>Fee Type</td>
                       <td>Fee Amount</td>
                       <td>Paid Amount</td>
                       <td>Balance</td>
                    </tr>
                    @foreach($invoices as $invoice)
                    <tr>
                       <td>{{ $invoice->feeType->name }}</td>
                       <td>{{ number_format($invoice->amount,2) }} {{ $invoice->currency }}</td>
                      <td>
                         @if(isset($invoice->gatewayPayment))
                            {{ number_format($invoice->gatewayPayment->paid_amount,2) }} TZS
                         @endif
                       </td>
                       <td>
                         @if(isset($invoice->gatewayPayment))
                            {{ number_format($invoice->gatewayPayment->bill_amount-$invoice->gatewayPayment->paid_amount,2) }} TZS
                         @endif
                       </td>
                    </tr>
                    @endforeach
                 </table>
                 <div class="ss-pagination-links">
                   {!! $invoices->render() !!}
                 </div>
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
