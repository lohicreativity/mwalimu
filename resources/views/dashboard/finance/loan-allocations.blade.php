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
            <h1>{{ __('Loan Beneficiaries') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Loan Beneficiaries') }}</li>
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
                <h3 class="card-title">Search for Loan Beneficiaries</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'finance/loan-bank-details','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Select year of study') !!}
                    <select name="year_of_study" class="form-control">
                       <option value="">Select Year of Study</option>
                       <option value="1">1</option>
                       <option value="2">2</option>
                       <option value="3">3</option>
                    </select>
                  </div>
                  </div>
                  
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($beneficiaries) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Loan Beneficiaries</h3><br>
                <a href="{{ url('finance/notify-loan-students?study_academic_year_id='.$request->get('study_academic_year_id').'&year_of_study='.$request->get('year_of_study')) }}" class="btn btn-primary">Notify Loan Beneficiaries</a>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                {!! Form::open(['url'=>'finance/loan-allocation-update-signatures','class'=>'ss-form-processing']) !!}
                  {!! Form::input('hidden','study_academic_year_id',$request->get('study_academic_year_id')) !!}
                  {!! Form::input('hidden','year_of_study',$request->get('year_of_study')) !!}
                <table class="table table-bordered ss-paginated-table">
                   <thead>
                     <tr>
                       <th>Index Number</th>
                       <th>Name</th>
                       <th>Sex</th>
                       <th>Phone </th>
                       <th>Total Amount (TZS)</th>
                       <th>Bank Name</th>
                       <th>Account Number</th>
                       <th>Has Signed?</th>
                     </tr>
                   </thead>
                   <tbody>
                     @foreach($beneficiaries as $stud)
                      <tr>
                        <td>{{ $stud->index_number }}</td>
                        <td>{{ $stud->first_name }} {{ $stud->middle_name? substr($stud->middle_name,0,1).'.': null }} {{ $stud->surname }} </td>					
                        <td>{{ $stud->sex }}</td>
                        <td>{{ $stud->phone }}</td>
                        <td>{{ number_format(($stud->tuition_fee + $stud->books_and_stationeries + $stud->meals_and_accomodation + $stud->field_training + $stud->research),2) }}</td>
                        <td>@if(isset($stud->student)) {{ $stud->student->bank_name }} @endif</td>
                        <td>@if(isset($stud->student)) {{ $stud->student->account_number }} @endif</td>
                        <td>
                          @if($stud->has_signed == 1)
                           <span style="color: red">Yes </span>
                          @else
                            {{ Form::checkbox('allocation_'.$stud->id,$stud->id) }}
                          @endif
                        </td>
                      </tr>
                     @endforeach
                      <tr>
                        <td colspan="7"><button type="submit" class="btn btn-primary">Update Signatures</button></td>
                      </tr>
                   </tbody>
                </table>
                {!! Form::close() !!}
              </div>
            </div>
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
