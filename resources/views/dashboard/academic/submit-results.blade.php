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
            <h1>{{ __('Submit Results to Regulators') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Submit Results to Regulators') }}</li>
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
                <h3 class="card-title">Submit Results to Regulators - @foreach($campuses as $campus) @if($campus->id == $campus_id) {{ $campus->name}} @endif @endforeach </h3>
              </div>
              <!-- /.card-header -->
              {!! Form::open(['url'=>'academic/results/process','class'=>'ss-form-processing']) !!}
              <div class="card-body">

                <div class="row">
                  <div class="form-group col-3">
                    {!! Form::label('','Academic Year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                      <option value="">Select Academic Year</option>
                      @foreach($study_academic_years as $year)
                        <option value="{{ $year->id }}" @if($year->status == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group col-3">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required>
                      <option value="">Select Semester</option>
                      @foreach($semesters as $semester)
                        @if($active_semester) 

                            <option value="{{ $semester->id }}" selected="selected">{{ $semester->name }}</option>

                        @endif
                      @endforeach

                          <option value="SUPPLEMENTARY">Supplementary</option>
                    </select>
                  </div>

                  <div class="form-group col-3">
                    {!! Form::label('','Intake') !!}
                    <select name="intake_id" class="form-control" required>
                      <option value="">Select Intake</option>
                      @foreach($intakes as $intake)
                        <option value="{{ $intake->id }}" @if($intake->name == 'September') selected="selected" @endif>{{ $intake->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="form-group col-3">
                    {!! Form::label('','Programme') !!}
                    <select name="program_level_id" class="form-control" required>
                    <option value="">Select Program Level</option>
                      @foreach($awards as $award)
                        @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor'))
                          <option value="{{ $award->id }}">{{ $award->name }}</option>
                        @endif
                      @endforeach
                    </select>
                  </div>
                </div>
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
