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
                 <table class="table table-bordered ss-paginated-table">
                    <thead>
                    <tr>
                       <th>Academic Year</th>
                       <th>Date</th>
                       <th>Control#</th>					   
                       <th>Fee Type</th>
                       <th>Fee Amount (TZS)</th>
                       <th>Paid Amount (TZS)</th>
                       <th>Balance (TZS)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $x = $y = null;
                      foreach($receipts as $receipt){
                        $fee_type = $ac_yr = null;
                        if($receipt->gatewayPayment){
                          $fee_type = $receipt->fee_type_id;
                          $ac_yr = $receipt->applicable;

                          if($fee_type == $x && $ac_yr == $y){
                            $total_paid_amount =+ $receipt->gatewayPayment->paid_amount;
                          }else{
                            
                          }

                        }
                      }
                    @endphp
                    @foreach($receipts as $receipt)
                    <tr>
                       <td>{{ explode('-',$receipt->applicable->begin_date)[0].'/'.explode('-',$receipt->applicable->begin_date)[0]+1 }}</td>
                       <td>{{ date('Y-m-d',strtotime($receipt->created_at))}}</td>
                       <td>{{ $receipt->control_no }}</td> 					   
                       <td>{{ $receipt->feeType->name }}</td> 
                       <td>

                        @if(str_contains($receipt->feeType->name,'Tuition'))
                          @if(count($tuition_fee_loans) > 0)
                            @foreach ($tuition_fee_loans as $tuition_fee_loan)
                              @if($tuition_fee_loan->study_academic_year_id == $receipt->applicable->id)
                                {{ number_format($receipt->amount,2) }} <span style="color: red">({{ number_format($tuition_fee_loan->tuition_fee,2) }} from HESLB) </span>
                                @break
                              @endif
                            @endforeach
                          @else
                            {{ number_format($receipt->amount,2) }}
                          @endif
                        @else
                        {{ number_format($receipt->amount,2) }}
                        
                        @endif

                       </td>
                       <td>
                          @if($receipt->gatewayPayment) {{ number_format($receipt->gatewayPayment->paid_amount,2) }} @else 0.00 @endif
                       </td>
                       <td>
                        @if($receipt->gatewayPayment) {{ number_format($receipt->amount-$receipt->gatewayPayment->paid_amount,2) }} @else {{ number_format($receipt->amount, 2) }}@endif
                       </td>
                    </tr>
                    @endforeach
                  </tbody>
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
