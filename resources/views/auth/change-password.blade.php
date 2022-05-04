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
            <h1 class="m-0">Change Password</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active"><a href="#">Change Password</a></li>
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
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Change Password') }}</h3>
              </div>
              <div class="card-body">
                 @if(isset($student))
                    @if($student->applicant->insurance_check != 1)
                     <div class="alert alert-warning">Please pay insurance charges to proceed with registration.</div>
                    @endif
                 @endif
                 @php
                    $old_password = array(
                        'placeholder'=>'Old password',
                        'class'=>'form-control',
                        'required'=>TRUE
                      );

                    $password = array(
                        'placeholder'=>'New assword',
                        'class'=>'form-control',
                        'required'=>TRUE
                      );

                    $password_confirmation = array(
                        'placeholder'=>'Password confirmation',
                        'class'=>'form-control',
                        'required'=>TRUE
                      );
                 @endphp

                 {!! Form::open(['url'=>'update-password','class'=>'ss-form-processing']) !!}

                 <div class="form-group col-6">
                   {!! Form::label('','Old password') !!}
                   {!! Form::password('old_password', $old_password) !!}
                 </div><!-- end of form-group -->
                 
                 <div class="form-group col-6">
                 {!! Form::label('','New password') !!}
                 {!! Form::password('password', $password) !!}
                 </div><!-- end of form-group -->

                 <div class="form-group col-6">
                 {!! Form::label('','Password confirmation') !!}
                 {!! Form::password('password_confirmation', $password_confirmation) !!}
                 </div><!-- end of form-group -->

                 <div class="ss-form-controls">
                   <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
                 </div>
                 {!! Form::close() !!}
              </div>
            </div>

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
