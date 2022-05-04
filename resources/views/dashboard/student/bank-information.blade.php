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
            <h1>{{ __('Bank Information') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Update Bank Information') }}</li>
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

            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Update Bank Information</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                    $bank_name = [
                       'class'=>'form-control',
                       'placeholder'=>'Bank name',
                       'required'=>true
                    ];

                    $account_number = [
                       'class'=>'form-control',
                       'placeholder'=>'Account number',
                       'required'=>true
                    ];
                 @endphp
                 {!! Form::open(['url'=>'student/update-bank-info','class'=>'ss-form-processing']) !!}

                 {!! Form::input('hidden','student_id',$student->id) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Bank name') !!}
                    {!! Form::text('bank_name',$student->bank_name,$bank_name) !!}
                  </div>
                </div>
                 <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Account number') !!}
                    {!! Form::text('account_number',$student->account_number,$account_number) !!}
                  </div>
                  
                  </div>
                  
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Update Bank Information') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
             

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
