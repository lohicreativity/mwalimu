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
                 {!! Form::open(['url'=>'finance/loan-beneficiaries','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
                <h3 class="card-title">List of Loan Beneficiaries</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                  
                <table class="table table-bordered">
                   <thead>
                     <tr>
                       <th>Index Number</th>
                       <th>Name</th>
                       <th>Gender</th>
                       <th>Tuition Fee</th>
                       <th>Books and Stationaries</th>
                       <th>Meals and Accomodation</th>
                       <th>Field Training</th>
                       <th>Research</th>
                       <th>Total Amount (TZS)</th>
                     </tr>
                   </thead>
                   <tbody>
                     @foreach($benefieciaries as $stud)
                      <tr>
                        <td>{{ $stud->index_number }}</td>
                        <td>{{ $stud->name }}</td>
                        <td>{{ $stud->sex }}</td>
                        <td>{{ $stud->tuition_fee }}</td>
                        <td>{{ $stud->books_and_stationaries }}</td>
                        <td>{{ $stud->meals_and_accomodation }}</td>
                        <td>{{ $stud->field_training }}</td>
                        <td>{{ $stud->reserch }}</td>
                        <td>{{ number_format($stud->loan_amount,2) }}</td>
                      </tr>
                     @endforeach
                   </tbody>
                </table>
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
