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
            <h1 class="m-0">Admission Confirmation - {{ $campus->name }}</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Submission</a></li>
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
            
            <!-- general form elements -->
            @if($applicant->multiple_admissions == 1)
            @if($applicant->confirmation_status == null)
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Admission Confirmation') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/confirm-admission','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                 @php
                    $confirmation_code = [
                       'placeholder'=>'Confirmation code',
                       'class'=>'form-control',
                       'required'=>true
                    ];
                 @endphp
                 <div class="form-group col-6">
                 {!! Form::label('','Confirmation code') !!}
                 {!! Form::text('confirmation_code',null,$confirmation_code) !!}

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                 </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
                </div>
              {!! Form::close() !!}
              </div>
            @elseif($applicant->confirmation_status == 'CONFIRMED')
              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Admission Confirmation') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/unconfirm-admission','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                 @php
                    $confirmation_code = [
                       'placeholder'=>'Confirmation code',
                       'class'=>'form-control',
                       'required'=>true
                    ];
                 @endphp
                 <div class="form-group col-6">
                 {!! Form::label('','Confirmation code') !!}
                 {!! Form::text('confirmation_code',null,$confirmation_code) !!}

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                 </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Unconfirm') }}</button>
                </div>
              {!! Form::close() !!}
              </div>
            @endif
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Request Confirmation Code') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/request-confirmation-code','class'=>'ss-form-processing']) !!}
                <div class="card-body">

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Confirmation Code') }}</button>
                </div>
              {!! Form::close() !!}
              </div>

              @endif

              @if($applicant->multiple_admissions == 0)
              @if($applicant->confirmation_status != 'CANCELLED')
              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Cancel Admission') }}</h3>
              </div>
              <div class="card-body">
              <h3 class="ss-color-success"><i class="fa fa-check-circle"></i> 
              Congratulations! You have been successfully selected.</h3>
              </div>
              </div>
              
              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Cancel Admission') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/cancel-admission','class'=>'ss-form-processing']) !!}
                <div class="card-body">

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                </div>
                <div class="card-footer">
                  <button @if($applicant->multiple_admissions == 0) disabled="disabled" @else type="submit" @endif class="btn btn-primary">{{ __('Cancel Admission') }}</button>
                </div>
              {!! Form::close() !!}
              </div>

              @elseif($applicant->confirmation_status == 'CANCELLED')
              <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Restore Cancelled Admission') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->

              {!! Form::open(['url'=>'application/restore-cancelled-admission','class'=>'ss-form-processing']) !!}
                <div class="card-body">

                 {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Restore Cancelled Admission') }}</button>
                </div>
              {!! Form::close() !!}
              </div>
              @endif
              @endif
          </div>
        </div>
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
