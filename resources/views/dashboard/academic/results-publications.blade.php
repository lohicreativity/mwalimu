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
            <h1>{{ __('Results Publications') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Result Publications') }}</li>
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

            @if(count($publications) != 0)
            <div class="card">
              <div class="card-header">
                 <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Programme Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('View Module Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-student-results') }}">{{ __('View Student Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($publications as $publication)
                  <tr>
                    <td>{{ $publication->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $publication->semester->name }}</td>
                    <td>{{ $publication->status }}</td>
                    <td>{{ $publication->type }}</td>
                    <td>
                      @if($publication->status == 'PUBLISHED')
                        <a class="btn btn-info btn-sm" href="{{ url('academic/result-publication/'.$publication->id.'/unpublish') }}">
                              <i class="fas fa-ban">
                              </i>
                              Unpublish
                       </a>
                      @else
                        <a class="btn btn-info btn-sm" href="{{ url('academic/result-publication/'.$publication->id.'/publish') }}">
                              <i class="fas fa-check-circle">
                              </i>
                              Publish
                       </a>
                      @endif
                      
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $publications->render() !!}
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
