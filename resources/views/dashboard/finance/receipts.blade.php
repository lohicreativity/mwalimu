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
            <h1>{{ __('Receipts') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Receipts') }}</li>
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
                <h3 class="card-title">{{ __('Receipts') }}</h3><br>
              </div>
              <div class="card-body">
                {!! Form::open(['url'=>'finance/receipts','method'=>'GET']) !!}
                <div class="input-group ss-stretch">
                 <input type="text" name="begin_date" class="form-control ss-datepicker" placeholder="Begin date">
                 <input type="text" name="end_date" class="form-control ss-datepicker" placeholder="Begin date">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>
                {!! Form::close() !!}
                  @if(count($receipts) != 0)
                    <table class="table table-bordered ss-paginated-table ss-margin-top">
                      <thead>
                        <tr>
                          <th>S/N</th>
                          <th>Reference #</th>
                          <th>Receipt #</th>
                          <th>Payer Name</th>
                          <th>Bank</th>
                          <th>Bill Amount</th>
                          <th>Paid Amount</th>
                          <th>Currency</th>
                          <th>Control Number</th>
                          <th>Date Created</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($receipts as $key=>$receipt)
                          <tr>
                           <td>{{ $key+1 }}</td>
                           <td>{{ $receipt->pay_refId }}</td>
                           <td>{{ $receipt->transaction_id }}</td>
                           <td>{{ $receipt->payer_name }}</td>
                           <td>{{ $receipt->bank }}</td>
                           <td>{{ number_format($receipt->amount,2) }}</td>
                           <td>{{ number_format($receipt->paid_amount,2) }}</td>
                           <td>{{ $receipt->currency }}</td>
                           <td>{{ $receipt->control_no }}</td>
                           <td>{{ $receipt->created_at }}</td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  @else
                     <h3>No receipts generated yet.</h3>
                  @endif
              </div>
          </div>
          </div><!-- end of row -->
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
