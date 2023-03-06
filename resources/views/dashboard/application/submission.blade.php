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
            <h1 class="m-0">Application Submission - {{ $campus->name }}</h1>
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
          @if($applicant->payment_complete_status == 0 && $applicant->is_transfered != 1)
            <div class="alert alert-warning">Payment section not completed</div>
            @else
              <div class="row">
                <div class="col-12">
            
              <!-- general form elements -->
              @if($applicant->submission_complete_status == 0)
              <div class="card card-default">
                <div class="card-header">
                  <h3 class="card-title">{{ __('Application Submission') }}</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->

                  {!! Form::open(['url'=>'application/submit-application','files'=>true,'class'=>'ss-form-processing']) !!}
                    <div class="card-body">

                      <p><a href="{{ url('application/summary') }}">Download Application Preview</a></p>

                      {!! Form::input('hidden','applicant_id',$applicant->id) !!}
                      <div class="form-group col-12">
                        <div class="custom-control custom-checkbox">
                              <input class="custom-control-input" type="checkbox" id="customCheckbox2" name="agreement_check" value="1" required>
                              <label for="customCheckbox2" class="custom-control-label">I have read and agreed to the terms of MNMA admission and that the information I have provided is true.</label>
                            </div>
                      </div>

                    </div>
                    <div class="card-footer">
                      <button type="submit" class="btn btn-primary">{{ __('Submit Application') }}</button>
                    </div>
                  {!! Form::close() !!}
              </div>
              @else

                @if($applicant->status == 'REJECTED' || $selected_status == true)
                <div class="card card-default">
                    <div class="card-header">
                      <h3 class="card-title">{{ __('Application Feedback') }}</h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                      <div class="card-body ss-center">
                        <div class="row">
                          <div class="col-12">
					                  @if($applicant->is_transfered != 1 && !$program_selection)
                              <div class="alert alert-danger" role="alert">
                                <h5><i class="fa fa-times-circle"></i> We are sorry to inform you that you have not been selected.</h5>
                              </div>
				                    @elseif($applicant->is_transfered != 1)
                              <div class="alert alert-danger" role="alert">
                                <h5><i class="fa fa-times-circle"></i> We are sorry to inform you that your transfer have not been successful.</h5>
                              </div>
					                  @endif
                          </div>
                        </div>
                      </div>
                </div>
                @else
                <div class="card card-default">
                    <div class="card-header">
                      <h3 class="card-title">{{ __('Application Submission') }}</h3>
                    </div>
                    <!-- /.card-header -->
                    <!-- form start -->
                    <div class="card-body ss-center">
                      <div class="row">
                        <div class="col-12">
                          <div class="alert alert-success" role="alert">
                            <h5><i class="fa fa-check-circle"></i> Your application is in progress.</h5>
                          </div>
                        </div>
                        
                        <div class="col-12">
                            <a href="{{ url('application/summary') }}" class="btn btn-primary">Download Application Preview</a>
                        </div>
                      </div>
                    </div>
                </div>
                @endif




              @endif
            </div>
          </div>
        @endif
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
