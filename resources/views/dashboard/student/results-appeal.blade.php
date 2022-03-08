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
            <h1 class="m-0">Results Appeal</h1>
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
        
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <div class="col-6">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Select number of subjects</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'student/get-control-number','class'=>'ss-form-processing']) !!}
                @php
                   $amount = [
                      'placeholder'=>'Amount',
                      'class'=>'form-control',
                      'readonly'=>true,
                      'required'=>true,
                      'id'=>'ss-amount'
                   ];
                @endphp
              <div class="card-body">
                 <div class="form-group">
                   {!! Form::label('','Fee type') !!}
                   <select name="fee_type_id" class="form-control" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-fee-type') }}" data-target="#ss-amount" id="ss-select-fee-type">
                      <option value="">Select Fee Type</option>
                      @foreach($fee_types as $type)
                      <option value="{{ $type->id }}">{{ $type->name }}</option>
                      @endforeach
                   </select>
                 </div>
                 {!! Form::input('hidden','payable_type','student') !!}
                 {!! Form::input('hidden','payable_id',$student->id) !!}

                 <div class="form-group">
                   {!! Form::label('','Select number of subjects') !!}
                   <select name="number_of_modules" class="form-control" id="ss-subjects-number">
                       <option value="1">1</option>
                       <option value="2">2</option>
                       <option value="3">3</option>
                       <option value="4">4</option>
                       <option value="5">5</option>
                       <option value="6">6</option>
                       <option value="7">7</option>
                       <option value="8">8</option>
                   </select>
                 </div>
                 <div class="form-group">
                   {!! Form::label('','Amount') !!}
                   {!! Form::text('amount',null,$amount) !!}
                 </div>
               </div>
                 <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Request Control Number') }}</button>
                </div>
                 {!! Form::close() !!}
            </div>
          </div>

        </div>
      </div>
          
          <div class="row">
          <!-- Left col -->
          <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">List of Control Numbers</h3>
            </div>
            <div class="card-body">
               <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Fee Item</th>
                      <th>Control Number</th>
                    </tr>
                  </thead>
                  <tbody>
                  @foreach($invoices as $invoice)
                  <tr>
                      <td>{{ $invoice->feeType->name }}</td>
                      <td>{{ $invoice->control_no }}</td>
                  </tr>
                  @endforeach
                  </tbody>
               </table>
            </div>
          </div>
          </div>
        </div>
          
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
