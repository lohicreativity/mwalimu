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
            <h1>{{ __('Assigned Modules') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Assigned Modules') }}</li>
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
                <h3 class="card-title">{{ __('Select Study Academic Year') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/staff-module-assignments','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                     
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($assignments) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Assigned Modules') }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Campus</th>
                    <th>Programme</th>
                    <th>Code</th>
                    <th>Module</th> 
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Credits</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($semesters as $semester)
                  @foreach($assignments as $assignment)
                  @if($assignment->programModuleAssignment->semester_id == $semester->id)
                  <tr>
                    <td>{{ $assignment->programModuleAssignment->campusProgram->campus->name }}</td>
                    <td>{{ $assignment->programModuleAssignment->campusProgram->program->name }}</td>
                    <td>{{ $assignment->programModuleAssignment->campusProgram->code }}</td>
                    <td><a href="{{ url('academic/staff-module-assignment/'.$assignment->id.'/assessment-plans') }}">{{ $assignment->module->name }} - {{ $assignment->module->code }}</a></td>
                    <td>{{ $assignment->programModuleAssignment->year_of_study }}</td>
                    <td>{{ $assignment->programModuleAssignment->semester->name }}</td>
                    <td>{{ $assignment->module->credit }}</td>
                  </tr>
                  @endif
                  @endforeach
                  @endforeach
                  </tbody>
                </table>
                <div class="ss-pagination-links">
                {!! $assignments->appends($request->except('page'))->render() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Staff Module Assignments') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <h3>No staff module assigned.</h3>
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
