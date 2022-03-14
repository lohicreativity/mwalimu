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
            <h1>{{ __('Graduants List') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Graduants List') }}</li>
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
                <div class="card-header">
                <ul class="nav nav-tabs">
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/run-graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Run Graduants') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Graduants List') }}</a></li>
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/excluded-graduants?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Excluded List') }}</a></li>
                </ul>
              </div>
            </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/run-graduants','class'=>'ss-form-processing','method'=>'GET']) !!}
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Select study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
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
            </div>
            <!-- /.card -->

            @if($campus && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Examination Policies - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/graduants/sort','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                   
                   <div class="row">
                   <div class="form-group col-6">
                    {!! Form::label('','Programme') !!}
                    <select name="campus_program_id" class="form-control" required>
                       <option value="">Select Programme</option>
                       @foreach($campus_programs as $program)
                          @if($program->campus_id == $campus->id)
                          <option value="{{ $program->id }}">{{ $program->program->name }}</option>
                          @endif
                       @endforeach
                    </select>
                  </div>
                </div>
                <div class="row">
                  <div class="form-group col-6">
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
