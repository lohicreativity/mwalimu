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
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results') }}">{{ __('Process Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results') }}">{{ __('View Programme Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results') }}">{{ __('View Module Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-student-results') }}">{{ __('View Student Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <h3>Name: {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }}</h3>

                 <table class="table table-bordered">
                   @foreach($years_of_studies as $key=>$years)
                   <tr>
                      <td>Year {{ $key }}</td>
                      <td>
                        @foreach($years as $yr)
                        <p class="ss-no-margin"><a href="{{ url('academic/results/'.$student->id.'/'.$yr->id.'/'.$key.'/show-student-results') }}">Results in Academic Year {{ $yr->academicYear->year}}</a></p>
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
