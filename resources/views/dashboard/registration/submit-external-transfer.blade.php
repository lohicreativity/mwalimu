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
            <h1>{{ __('External Transfer') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('External Transfer') }}</li>
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
                <h3 class="card-title">Search for Student</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $registration_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Registration number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'registration/external-transfer','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                  <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Enter student\'s registration number') !!}
                    {!! Form::text('registration_number',null,$registration_number) !!}
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->


            @if($student)
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Submit External Transfer</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'registration/submit-external-transfer','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 <table class="table table-bordered">
                    <tr>
                       <td>Student:</td>
                       <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</td>
                    </tr>
                    <tr>
                       <td>Index Number:</td>
                       <td>{{ $student->index_number }}</td>
                    </tr>
                    <tr>
                       <td>Current Programme:</td>
                       <td>
                          {{ $student->campusProgram->program->name }}
                       </td>
                    </tr>
                 </table><br>
                 <div class="form-group ss-margin-top">
                   {!! Form::label('','Enter new programme code') !!}
                   {!! Form::text('program_code',null,['class'=>'form-control','placeholder'=>'Programme code','required'=>true]) !!}
                 </div>  
                 {!! Form::input('hidden','student_id',$student->id) !!}
              </div>
              <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Submit External Transfer') }}</button>
              </div>
              {!! Form::close() !!}
            </div>
            @else
            @if(count($transfers) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">External Transfers</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                   <table class="table table-bordered" id="ss-transfers">
                     <thead>
                       <tr>
                         <th>Name</th>
                         <th>Registration Number</th>
                         <th>Programme</th>
                         <th>Date Transfered</th>
                         <th>Transfered By</th>
                       </tr>
                     </thead>
                     <tbody>
                      @foreach($transfers as $transfer)
                       <tr>
                         <td>{{ $transfer->student->first_name }} {{ $transfer->student->middle_name }} {{ $transfer->student->surname }}</td>
                         <td>{{ $transfer->student->registration_number }}</td>
                         <td>{{ $transfer->previousProgram->program->name }}</td>
                         <td>{{ $transfer->created_at }}</td>
                         <td>{{ $transfer->user->staff->first_name }} {{ $transfer->user->staff->surname }}</td>
                       </tr>
                       @endforeach
                     </tbody>
                   </table>

                   <div class="ss-pagination-links">
                      {!! $transfers->render() !!}
                   </div> 
              </div>
            </div>
            @endif
            @endif

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
