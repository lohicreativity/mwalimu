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
            <h1>{{ __('Results Publishing') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Result Publishing') }}</li>
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
                  @can('process-examination-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
                  @endcan
                  @can('view-programme-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Programme Results') }}</a></li>
                  @endcan
                  @can('view-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Module Results') }}</a></li>
                  @endcan
                  @can('view-student-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/show-student-results') }}">{{ __('Student Results') }}</a></li>
                  @endcan
                  @can('view-publish-examination-results')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  @endcan
                  @can('view-uploaded-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                  @endcan
                  @can('upload-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/upload-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Upload Module Results') }}</a></li>
                  @endcan
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>NTA Level</th>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))
                    <th>Campus</th>
                    @endif
                    <th>Status</th>
                    <th>Type</th>
                    @can('publish-examination-results')
                    <th>Actions</th>
                    @endcan
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($publications as $key=>$publication)
                  <tr>
                    <td>{{ ($key+1) }}</td>
                    <td>{{ $publication->studyAcademicYear->academicYear->year }}</td>
                    <td>@if($publication->semester) {{ $publication->semester->name }} @else Supplementary @endif</td>
                    <td>{{ $publication->ntaLevel->name }}</td>
                    @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('arc'))
                    <td>{{ $publication->campus->name }}</td>
                    @endif
                    <td>@if($publication->status != 'PUBLISHED') <span style='color:red'>{{ $publication->status }} @else {{ $publication->status }} @endif</td>
                    <td>{{ $publication->type }}</td>
                    @can('publish-examination-results')
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
                    @endcan
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
