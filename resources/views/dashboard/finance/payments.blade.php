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
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>{{ __('Payments') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Payments') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            @if(count($payments) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Payments') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                {!! Form::open(['url'=>'finance/payments','method'=>'GET']) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for receipt or reference or control number">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}
                <table id="example2" class="table table-bordered table-hover ss-margin-top">
                  <thead>
                  <tr>
                    <th>Invoice Number</th>
                    <th>Payer</th>
                    <th>Payer ID</th>
                    <th>For</th>
                    <th>Amount</th>
                    <th>Reference Number</th>
                    <th>Control Number</th>
                    <th>Receipt Number</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($payments as $payment)
                  <tr>
                    <td>{{ $payment->invoice_number }}</td>
                    <td>{{ $payment->usable->first_name }} {{ $payment->usable->middle_name }} {{ $payment->usable->surname }}</td>
                    <td>{{ $payment->usable->registration_number }}</td>
                    <td>{{ $payment->feeType->name }}</td>
                    <td>{{ number_format($payment->amount,2) }} {{ $payment->currency }}</td>
                    <td>{{ $payment->reference_number }}</td>
                    <td>{{ $payment->control_number }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $payments->appends($request->except('page'))->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @endif
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
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
