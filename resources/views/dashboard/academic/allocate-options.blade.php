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
            <h1>{{ __('Options Allocations') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Options Allocation') }}</li>
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
                <h3 class="card-title">Select Study Academic Year</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/options-allocations','class'=>'ss-form-processing','method'=>'GET']) !!}
                   <div class="row">
                   <div class="form-group col-6">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}" @if($request->get('semester_id') == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                       @endforeach
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

            @if($study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Allocate Options - {{ $study_academic_year->academicYear->year }} - {{ $staff->department->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/allocate-options','class'=>'ss-form-processing']) !!}
                   
                   <div class="form-group">
                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','department_id',$staff->department_id) !!}
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Allocate Options') }}</button>
                  </div>

                 {!! Form::close() !!}

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
