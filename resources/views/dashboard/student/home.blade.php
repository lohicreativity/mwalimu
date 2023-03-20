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
            <h1 class="m-0">Welcome, {{ $student->first_name }} {{ $student->surname }}</h1>
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
		
			@if($transcript_request_status != null)
		
			  <div class="alert alert-success col-12">
			  <h3 class="text-white" style="font-size:13pt!important;"><i class="fa fa-check-circle"></i> 
			  Your transcript is in final stages of completion, please collect it 5 days from {{ $transcript_request_status->updated_at->format('d/m/Y')}}. </h3>
			  </div>
		
			@endif
		
		
		
		
          @if($loan_allocation)
            @if($student->account_number == null)
              <div class="alert alert-warning">Please provide Bank information.</div>
            @endif
            @if($loan_allocation->notification_sent == 1 && $loan_allocation->has_signed != 1)
              <div class="alert alert-warning">Please visit loans office for signing your loan payment.</div>
            @endif
          @endif
          @if($performance_report)
            <div class="alert alert-success">Your performance report is ready.</div>
          @endif
          {{--
          @if(!$registration)
          <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registration</h3>
              </div>
              <div class="card-body">
                 
                   <div class="alert alert-warning">
                     <h4>You are not registered yet for this semester.</h4>
                   </div>
            
              </div>
              <div class="card-footer">
                 <a href="#" data-toggle="modal" data-target="#ss-insurance-card" class="btn btn-primary">Register</a>
              </div>
            </div>

            <div class="modal fade" id="ss-insurance-card">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"> Insurance Cards</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-12">
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-nhif-form" id="ss-card-nhif"> NHIF
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-other-form" id="ss-card-other"> Other Insurers
                        </label>
                        <label class="radio-inline">
                          <input type="radio" name="insurance_card" value="#ss-card-none-form" id="ss-card-none"> Don't have Insurance
                        </label>
                        </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->
                     <div class="row" id="ss-card-nhif-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'registration/verify-nhif','class'=>'ss-form-processing']) !!}
                            <div class="form-group">
                              {!! Form::label('','Card number') !!}
                              {!! Form::text('card_number',null,['class'=>'form-control','placeholder'=>'Card number']) !!}
                            </div>
                            <button type="submit" class="btn btn-primary">Verify</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     <div class="row" id="ss-card-other-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'registration/store-other-card','class'=>'ss-form-processing']) !!}
                            <div class="form-group">
                              {!! Form::label('','Insurance company') !!}
                              {!! Form::text('company',null,['class'=>'form-control','placeholder'=>'Insurance company']) !!}
                            </div>
                            <div class="form-group">
                              {!! Form::label('','Card Number') !!}
                              {!! Form::text('card_number',null,['class'=>'form-control','placeholder'=>'Card number']) !!}
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                     <div class="row" id="ss-card-none-form">
                      <div class="col-12">
                          {!! Form::open(['url'=>'registration/request-nhif','class'=>'ss-form-processing']) !!}
                            <button type="submit" class="btn btn-primary">Request NHIF</button>
                          {!! Form::close() !!}
                       </div><!-- end of col-md-12 -->
                     </div><!-- end of row -->

                  </div>
                  <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </div>
                <!-- /.modal-content -->
              </div>
              <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
          @endif
          --}}
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          
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
