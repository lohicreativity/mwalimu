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
            <h1 class="m-0">Postponement Request</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item active"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Postponement Request</a></li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
     <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Postponement Request') }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'application/request-postponement','files'=>true,'class'=>'ss-form-processing']) !!}
              <div class="card-body">
                  {!! Form::input('hidden','applicant_id',$applicant->id) !!}

                  {!! Form::label('','Upload postponement letter') !!}
                  {!! Form::file('letter',['class'=>'form-control','required'=>true]) !!}
              </div>
              <div class="card-footer">
              <button @if($program_fee_invoice) disabled="disabled" @else type="submit" @endif class="btn btn-primary">{{ __('Submit Postponement Request') }}</button>
            </div>
            {!! Form::close() !!}
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
