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
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/results/show-program-results?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Programme Results') }}</a></li>
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
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/results/show-program-results','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       @if(session('staff_campus_id') == $cp->id)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endif
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
                <h3 class="card-title">View Results for {{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/results/show-program-report','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                   
                   <div class="row">
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
                       <option value="{{ $semester->id }}" @if($semester->status == 'ACTIVE') selected="selected" @endif>{{ $semester->name }}</option>
                       @endforeach
                       <option value="SUPPLEMENTARY">Supplementary</option>
                       <option value="SUPP-SPECIAL">Supplementary (Special)</option>
                       <option value="ANNUAL">Annual</option>                       
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
                  <div class="row">
                  <div class="form-group col-12">
                        <label class="radio-inline">
                          <input type="radio" name="name_display_type" id="inlineRadio2" value="SHOW" checked="checked"> Show Names
                        </label>&nbsp;
                        <label class="radio-inline">
                          <input type="radio" name="name_display_type" id="inlineRadio3" value="HIDE"> Hide Names
                        </label>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-12">
                        <label class="radio-inline">
                          <input type="radio" name="reg_display_type" id="inlineRadio2" value="SHOW" checked="checked"> Show Reg Number
                        </label>&nbsp;
                        <label class="radio-inline">
                          <input type="radio" name="reg_display_type" id="inlineRadio3" value="HIDE"> Hide Reg Number
                        </label>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-12">
                        <label class="radio-inline">
                          <input type="radio" name="gender_display_type" id="inlineRadio2" value="SHOW" checked="checked"> Show Gender
                        </label>&nbsp;
                        <label class="radio-inline">
                          <input type="radio" name="gender_display_type" id="inlineRadio3" value="HIDE"> Hide Gender
                        </label>
                  </div>
                </div>
              </div>
               <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('View Results') }}</button>
                </div>
              {!! Form::close() !!}
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
