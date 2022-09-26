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
            <h1>{{ __('Programme Module Assignment') }} - {{ $campus_program->program->name }} - {{ $study_academic_year->academicYear->year }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programme Module Assignment') }}</li>
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

            @if(count($assignments) != 0 && $campus_program && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Programme Modules') }} - {{ $campus_program->program->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Year</th>
                    <th>Credits</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($assignments as $assignment)
                  <tr>
                    <td>{{ $assignment->module->name }}</td>
                    <td>{{ $assignment->module->code }}</td>
                    <td>{{ $assignment->year_of_study }}</td>
                    <td>{{ $assignment->module->credit }}</td>
                    <td>{{ $assignment->semester->name }}</td>
                    <td>{{ $assignment->category }}</td>
                    <td>{{ $assignment->type }}</td>
                    <td>
                      @can('add-examination-irregularities')
                      <a class="btn btn-info btn-sm" href="{{ url('academic/module-assignment/'.$assignment->id.'/examination-irregularities') }}">
                              <i class="fas fa-random">
                              </i>
                               Assign Irregularities
                       </a>
                       @endcan
                    </td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Module Assigned') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
              </div>
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
