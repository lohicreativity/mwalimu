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
                <h3 class="card-title">Examination Results</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 <h3>Name: {{ $student->surname }}, {{ $student->first_name }} {{ $student->middle_name }}</h3>

                 <table class="table table-bordered">
                   @foreach($years_of_studies as $key=>$years)
                   <tr>
                      @if(count($years) > 1)
                      <td><a href="{{ url('student/results/'.null.'/'.$years[0]->id.'/'.$key.'/show-student-overall-results?next_ac_yr_id='.$years[1]->id) }}">Overall Results for Year {{ $key }}</a></td>
                      @else
                      <td><a href="{{ url('student/results/'.null.'/'.$years[0]->id.'/'.$key.'/show-student-overall-results?next_ac_yr_id=') }}">Overall Results for Year {{ $key }}</a></td>
                      @endif
                      <td>
                        @foreach($years as $yr)
                        <p class="ss-no-margin"><a href="{{ url('student/results/'.$yr->id.'/'.$key.'/report') }}">Results in Academic Year {{ $yr->academicYear->year}}</a></p>
                        @endforeach
                      </td>
                      @if($results_present_status)
                      <td><a class="btn btn-primary" href="{{ url('student/request-performance-report?year_of_study='.$key.'&study_academic_year_id='.$years[0]->id) }}">Request Annual Statement of Results</a> <span style="color:red"> Payment is Required </span></td>
                      @endif
                   </tr>
                   @endforeach
                   <tr>
                     <td></td>
                     <td></td>
                     @if(count($years_of_studies) == 3)
                     <td>
                       <a class="btn btn-primary" href="{{ url('student/request-performance-report?type=overall') }}">Request Overall Statement of Results</a> <span style="color:red"> Payment is Required </span>
                     </td>
                     @endif
                   </tr>
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
