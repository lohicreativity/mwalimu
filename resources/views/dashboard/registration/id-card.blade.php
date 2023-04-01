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
            <h1>{{ __('Student Search') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Student Search') }}</li>
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
              <!-- /.card-header -->

                 <div class="card-body">
                 {!! Form::open(['url'=>'registration/print-id-card','class'=>'ss-form-processing','method'=>'GET']) !!}

                  @if(Auth::user()->hasRole('administrator'))
                   <div class="row">
                   <div class="form-group col-4">
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Programme level') !!}
                    <select name="program_level_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                      <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group col-4">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($request->get('campus_id') == $cp->id) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                  </div>
                   @else
                   <div class="row">
                   <div class="form-group col-6">
				    {!! Form::input('hidden','campus_id',$request->get('campus_id')) !!}
                    {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($request->get('study_academic_year_id') == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                  <div class="form-group col-6">
                    {!! Form::label('','Programme level') !!}
                    <select name="program_level_id" class="form-control" required>
                      <option value="">Select Programme Level</option>
                      @foreach($awards as $award)
                      @if(str_contains($award->name,'Basic') || str_contains($award->name,'Ordinary') || str_contains($award->name,'Bachelor') || str_contains($award->name,'Masters'))
                      <option value="{{ $award->id }}" @if($request->get('program_level_id') == $award->id) selected="selected" @endif>{{ $award->name }}</option>
                      @endif
                      @endforeach
                    </select>
                  </div>
                  </div>
                  @endif
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>
				  
                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($students) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Students Awaiting IDs - {{ $study_academic_year->academicYear->year }}</h3><br>

              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <div class="input-group ss-stretch">
                 <input type="text" name="query" class="form-control" placeholder="Search for student name">
                 <span class="input-group-btn">
                   <button class="btn btn-default" type="submit"><span class="fa fa-search"></span></button>
                 </span>
                </div>

                <table id="example2" class="table table-bordered table-hover ss-margin-top">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Reg. No.</th>
                    <th>Names</th>
                    <th>Sex</th>
                    <th>Phone</th>
                    <th>Programme</th>
                    @if(Auth::user()->hasRole('admission-officer'))
                    <th>Action</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>

					@foreach($students as $key=>$student)
						<tr>
						  <td>{{ ($key+1) }}</td>
						  <td>{{ $student->registration_number }}</td>
						  <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</td>
						  <td>{{ $student->gender }}</td>
						  <td>{{ $student->phone }}</td>
						  <td>{{ $student->campusProgram->program->code }}</td>
						  <td>
							@if(Auth::user()->hasRole('admission-officer'))
							  <a class="btn btn-success btn-sm" href="#" data-toggle="modal" data-target="#ss-student-id-{{ $student->id }}">
									  <i class="fas fa-check">
									  </i>
									  Priview ID
							  </a>
							@endif
						  </td>
						</tr>


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
