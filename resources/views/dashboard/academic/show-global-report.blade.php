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
            <h1>{{ __('Global Report') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Global Report') }}</li>
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
                <h3 class="card-title">Global Report</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results/get-global-report','class'=>'ss-form-processing']) !!}
                   <div class='row'>
                   <div class="form-group col-6">
                      <select name="study_academic_year_id" class="form-control" required>
                        <option value="">Select Study Academic Year</option>
                        @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}" @if($year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group col-6">
                      <select name="semester_id" class="form-control" required>
                        <option value="">Select Results Type</option>
                        @foreach($semesters as $semester)
                          @if($active_semester) 
                            <option value="{{ $semester->id }}" @if($active_semester->id == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                          @endif
                        @endforeach
                        @if($active_semester) 
                          <option value="ANNUAL">Annual Results</option>
                        @endif
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
