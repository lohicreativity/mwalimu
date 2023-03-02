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
                <ul class="nav nav-tabs">
                  @can('process-examination-results')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Process Results') }}</a></li>
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
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results-publications') }}">{{ __('Publish Results') }}</a></li>
                  @endcan
                  @can('view-uploaded-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/uploaded-modules?campus_id='.session('staff_campus_id')) }}">{{ __('Uploaded Modules') }}</a></li>
                  @endcan
                  @can('upload-module-results')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/results/upload-module-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Upload Module Results') }}</a></li>
                  @endcan

                </ul>
              </div>
              <!-- /.card-header -->
              @if(!session('active_academic_year_id'))
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->status == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
              @endif
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
                    <select name="programme_level" class="form-control" required>
                       <option value="">Select Programme Level</option>
                       <option value="Certificate">Basic Technician Certificate</option>
                       <option value="Diploma">Ordinary Diploma</option>
                       <option value="Bachelor">Bachelor Degree</option>
                       <option value="Masters">Masters Degree</option>
                    </select>
                  </div>

                  <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                       <option value="">Select Programme</option>
                       @foreach($campus_programs as $program)
                          @if(App\Utils\Util::collectionContainsKey($program->program->departments,$staff->department_id))
                          @for($i = 1; $i <= $program->program->min_duration; $i++)
                          <option value="{{ $program->id }}_year_{{ $i }}">{{ $program->program->name }} - Year {{ $i }}</option>
                          @endfor
                          @endif
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
                       @if($active_semester) 
                        @if($active_semester->id == $semester->id) 
                       <option value="{{ $semester->id }}" selected="selected">{{ $semester->name }}</option>
                       @endif
                       @endif
                       @endforeach
                       @if($active_semester) 
                        @if(App\Utils\Util::stripSpacesUpper($active_semester->name) == App\Utils\Util::stripSpacesUpper('Semester 2') && $second_semester_publish_status)
                       <option value="SUPPLEMENTARY" selected="selected">Supplementary</option>
                       @endif
                       @endif
                    </select>
                    {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                    {!! Form::input('hidden','campus_id',$campus->id) !!}
                  </div>
                  </div>
                <div class="row">
                  <div class="form-group col-6">
                     {!! Form::label('','Intake') !!}
                     <select name="intake_id" class="form-control" required>
                       <option value="">Select Intake</option>
                       @foreach($intakes as $intake)
                       <option value="{{ $intake->id }}" @if($intake->name == 'September') selected="selected" @endif>{{ $intake->name }}</option>
                       @endforeach
                     </select>
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
                <h3 class="card-title">{{ __('Results Publishing Status') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
					<th>NTA Level</th>
                    <th>Status</th>
                    <th>Type</th>
                    
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($publications as $publication)
                  <tr>
                    <td>{{ $publication->studyAcademicYear->academicYear->year }}</td>
                    <td>@if($publication->semester) {{ $publication->semester->name }} @else Supplementary @endif</td>
					<td>{{ $publication->ntaLevel->name }}</td>
                    <td>{{ $publication->status }}</td>
                    <td>{{ $publication->type }}</td>
                  </tr>
                  @endforeach
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            @endif

             @if(count($process_records) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Results Process Records') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Semester</th>
                    <th>Year</th>
                    <th>Date</th>
                 </tr>
               </thead>
               <tbody>
                  @foreach($process_records as $record)
                  <tr>
                     <td>{{ $record->campusProgram->program->name }}</td>
                     <td>@if($record->semester) {{ $record->semester->name }} @else Supplementary @endif</td>
                     <td>{{ $record->year_of_study }}</td>
                     <td>{{ $record->created_at }}</td>
                  </tr>
                  @endforeach
               </tbody>
              </table>

              <div class="ss-pagination-links">
                  {!! $process_records->render() !!}
              </div>
            </div>
          </div>
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
