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
            <h1 class="m-0">Request Control Number</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
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
        
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Request Control Number</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'student/get-control-number','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 <label class="radio-inline">
                          <input type="radio" name="fee_type" value="TUITION" id="ss-card-other"> Tuition Fee
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="fee_type" value="LOST ID" id="ss-card-none"> Lost Identity Card
                        </label>

                 {!! Form::input('hidden','student_id',$student->id) !!}

               </div>
                 <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Control Number') }}</button>
                </div>
                 {!! Form::close() !!}
            </div>
          </div>

        </div>
      </div>
          
          <div class="row">
          <!-- Left col -->
          <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">List of Control Numbers</h3>
            </div>
            <div class="card-body">
               <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Fee Item</th>
                      <th>Amount</th>
                      <th>Amount to be paid</th>
                      <th>Currency</th>
                      <th>Control Number</th>
                      <th>Status</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach($invoices as $invoice)
                  <tr>
                      <td>{{ $invoice->feeType->name }}</td>
                      <td>{{ number_format($invoice->actual_amount,0) }}</td>
                      <td>{{ number_format($invoice->amount,0) }}</td>
                      <td>{{ $invoice->currency }}</td>
                      <td>{{ $invoice->control_no }} @if(!$invoice->control_no)<a href="#" onclick="window.location.reload();"><i class="fa fa-refresh" ></i> Refresh</a>@endif</td>
                      <td>
                        @if($invoice->gatewayPayment && $invoice->control_no)
                         <span class="badge badge-success">Paid</span>
                        @elseif($invoice->control_no)
                         <span class="badge badge-warning">Unpaid</span>
                        @endif
                      </td>
                      <td>{{ date('Y-m-d',strtotime($invoice->created_at)) }}</td>
                  </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
          </div>
          </div>
        </div>
          
        </div>
        <!-- /.row (main row) -->
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
