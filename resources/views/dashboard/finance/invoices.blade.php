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
            <h1>{{ __('Invoices') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Invoices') }}</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
         <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Invoices') }}</h3><br>
              </div>
              <div class="card-body">
               {!! Form::open(['url'=>'finance/invoices','method'=>'GET']) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="begin_date" class="form-control ss-datepicker" placeholder="Begin date">
                 <input type="text" name="end_date" class="form-control ss-datepicker" placeholder="Begin date">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}
                  @if(count($invoices) != 0)
                    <table class="table table-bordered ss-paginated-table ss-margin-top">
                      <thead>
                        <tr>
                          <th>S/N</th>
                          <th>Customer ID</th>
                          <th>Reference #</th>
                          <th>Payer Name</th>
                          <th>Bill Type</th>
                          <th>Bill Amount</th>
                          <th>Control Number</th>
                          <th>Date Created</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($invoices as $key=>$invoice)
                          <tr>
                           <td>{{ $key+1 }}</td>
                           <td>
                               @php
                                  if($invoice->payable_type == 'student'){
                                  $stud_reg = substr($invoice->payable->registration_number, 5);
                                  $stud_reg = str_replace('/', '', $stud_reg);
                                  $parts = explode('.', $stud_reg);
                                  if($parts[0] == 'BTC'){
                                      $stud_reg = 'BT'.$parts[1];
                                  }else{
                                      $stud_reg = $parts[0].$parts[1];
                                  }
                                  }else{
                                      $stud_reg = null;
                                  }
                               @endphp
                               @if($stud_reg) {{ $stud_reg }} @else N/A @endif
                           </td>
                           <td>{{ $invoice->reference_no }}</td>
                           <td>{{ $invoice->payable->first_name }} {{ $invoice->payable->middle_name }} {{ $invoice->payable->surname }}</td>
                           <td>{{ $invoice->feeType->name }}</td>
                           <td>{{ number_format($invoice->amount,2) }}</td>
                           <td>{{ $invoice->control_no }} @if(!$invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif</td>
                           <td>{{ $invoice->created_at }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @else
                     <h3>No invoices generated yet.</h3>
                  @endif
              </div>
          </div>
          </div><!-- end of card -->
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
