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
            <h1>{{ __('Examination Results Processing') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Examination Results Processing') }}</li>
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
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules') }}">{{ __('Uploaded Modules') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}">{{ $cp->name }}</option>
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
             
            @if($study_academic_year && $campus)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Process Results for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/results/process','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                       <option value="">Select Programme</option>
                       @foreach($campus_programs as $program)
                          @for($i = 1; $i <= $program->program->min_duration; $i++)
                          <option value="{{ $program->id }}_year_{{ $i }}">{{ $program->program->name }} - Year {{ $i }}</option>
                          @endfor
                       @endforeach
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-6">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                       @endforeach
                       <option value="SUPPLEMENTARY">Supplementary</option>
                    </select>
                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                  </div>
                  </div>
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Process Results') }}</button>
                </div>
              {!! Form::close() !!}
             </div>
            @endif


            @if(count($publications) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Results Publications') }}</h3>
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
                    <td>@if($publication->semester) {{ $publication->semester->name }} @else SUPP @endif</td>
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
