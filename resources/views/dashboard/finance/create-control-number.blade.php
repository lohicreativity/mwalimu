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
            <h1>{{ __('Create Control Numbers') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Create Control Numbers') }}</li>
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
            <!-- general form elements -->
             @can('create-control-number')
            <div class="card card-default">
              <div class="card-header">
                <h3 class="card-title">{{ __('Request Control Number') }}</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
              @php
                  $registration_number = [
                     'placeholder'=>'MNMA/BTC.COD/0000/23',
                     'class'=>'form-control',
                     'required'=>true
                  ];
              @endphp
              {!! Form::open(['url'=>'finance/request-control-number','class'=>'ss-form-processing']) !!}
                <div class="card-body">
                  <div class="row">
                  <div class="form-group col-4">
                    {!! Form::label('','Registration Number') !!}
                    {!! Form::text('registration_number',null,$registration_number) !!}
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Payment Item') !!}
                    <select name="fee_type_id" class="form-control">
                      <option value="">Select Payment Item</option>
                      @foreach($fee_types as $types)
                        <option value="{{ $types->id }}">{{ $types->name }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control">
                      <option value="">Select Study Academic Year</option>
                      @foreach($study_academic_years as $k=>$year)
                        <option value="{{ $year->id }}" @if($k == 0 || $year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Control Number') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan

            @if($student)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('List of Control Number Requests') }}</h3><br>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>			  
                    <th>Registration#</th>
                    <th>Names</th>
                    <th>Gender</th>
                    <th>Phone</th>					
                    <th>Status</th>
                    <th>Payment Item</th>
                    <th>Amount</th>
                    <th>Control#</th>
                    <th>Validity</th>					
                  </tr>
                  </thead>
                  <tbody>
                  <tr>
                    <td>{{ $student->registration_number }}</td>
                    <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ $student->phone }}</td>					
                    <td style="color:red">{{ $student->studentShipStatus->name }}</td>
                    <td>{{ $invoice->feeType->name }}</td>
                    <td>{{ $invoice->currency }} {{$invoice->actual_amount }}</td>
                    <td>{{ $invoice->control_no }}</td>		
                    <td>{{ $invoice->feeType->duration }} Days</td>						
                  </tbody>
                </table>
                <div class="ss-pagination-links">

                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
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
