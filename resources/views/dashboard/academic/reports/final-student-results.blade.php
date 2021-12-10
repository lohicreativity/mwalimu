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
            <h1>{{ __('Examination Results') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Examination Results') }}</li>
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
                <ul class="nav nav-tabs">
                  @can('process-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
                  @endcan
                  @can('view-programme-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Programme Results') }}</a></li>
                  @endcan
                  @can('view-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Module Results') }}</a></li>
                  @endcan
                  @can('view-student-results')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results/show-student-results') }}">{{ __('View Student Results') }}</a></li>
                  @endcan
                  @can('publish-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  @endcan
                  @can('view-uploaded-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                  @endcan
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <p class="ss-no-margin">Student Name: {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }}</p>
                 <p class="ss-no-margin">Programme: {{ $student->campusProgram->program->name }}</p>
                 <p class="ss-no-margin">Registration Number: {{ $student->registration_number }}</p>
                 <p class="ss-no-margin">Year of Study: {{ $student->year_of_study }}</p>

                 <table class="table table-bordered">
                   @foreach($years_of_studies as $key=>$years)
                   <tr>
                      <td>Overall Results for Year {{ $key }}</td>
                      <td>
                        @foreach($years as $yr)
                        <p class="ss-no-margin"><a href="{{ url('academic/results/'.$student->id.'/'.$yr->id.'/'.$key.'/show-student-results') }}">Results in Academic Year ({{ $yr->academicYear->year}})</a></p>
                        @endforeach
                      </td>
                   </tr>
                   @endforeach
                 </table>

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
