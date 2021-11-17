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
            <h1>{{ __('Program Module Assignment') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Program Module Assignment') }}</li>
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
                 @php
                    $year_of_study = [
                       'placeholder'=>'Year of study',
                       'class'=>'form-control',
                       'required'=>true
                    ];
                 @endphp

                 {!! Form::open(['url'=>'academic/program-module-assignment/store','class'=>'ss-form-processing']) !!}
                   
                   <div class="row">
                   <div class="form-group col-8">
                    {!! Form::label('','Module') !!}
                    <select name="module_id" class="form-control" required>
                       <option value="">Select Module</option>
                       @foreach($modules as $module)
                       <option value="{{ $module->id }}">{{ $module->name }}</option>
                       @endforeach
                    </select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                       @endforeach
                    </select>
                    </div>
                  </div>
                    <div class="row">
                    <div class="form-group col-6">
                    {!! Form::label('','Category') !!}
                    <select name="category" class="form-control" required>
                       <option value="CORE">Core</option>
                       <option value="OPTIONAL">Optional</option>
                       <option value="FUNDAMENTAL">Fundamental</option>
                    </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Year of study') !!}
                      {!! Form::input('number','year_of_study',null,$year_of_study) !!}
                      
                    </div>
                    <div class="form-group">
                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  {!! Form::input('hidden','campus_program_id',$campus_program->id) !!}
                </div>
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Assign Program Module') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->

            @if(count($assignments) != 0 && $campus_program && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Program Module Assignment') }} - {{ $campus_program->program->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Module</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($assignments as $assignment)
                  <tr>
                    <td>{{ $assignment->module->name }}</td>
                    <td>{{ $assignment->year_of_study }}</td>
                    <td>{{ $assignment->semester->name }}</td>
                    <td>{{ $assignment->category }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-module-assignment-{{ $assignment->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>

                       <div class="modal fade" id="ss-edit-module-assignment-{{ $assignment->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Program Module Assignment</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                                {!! Form::open(['url'=>'academic/program-module-assignment/update','class'=>'ss-form-processing']) !!}

                                   <div class="row">
                   <div class="form-group col-8">
                    {!! Form::label('','Module') !!}
                    <select name="module_id" class="form-control" required>
                       <option value="">Select Module</option>
                       @foreach($modules as $module)
                       <option value="{{ $module->id }}" @if($assignment->module_id == $module->id) selected="selected" @endif>{{ $module->name }}</option>
                       @endforeach
                    </select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}" @if($assignment->semester_id = $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                       @endforeach
                    </select>
                    </div>
                  </div>
                    <div class="row">
                    <div class="form-group col-6">
                    {!! Form::label('','Category') !!}
                    <select name="category" class="form-control" required>
                       <option value="CORE" @if($assignment->category == 'CORE') selected="selected" @endif>Core</option>
                       <option value="OPTIONAL" @if($assignment->category == 'OPTIONAL') selected="selected" @endif>Optional</option>
                       <option value="FUNDAMENTAL" @if($assignment->category == 'FUNDAMENTAL') selected="selected" @endif>Fundamental</option>
                    </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Year of study') !!}
                      {!! Form::input('number','year_of_study',$assignment->year_of_study,$year_of_study) !!}
                      
                    </div>
                                    <div class="form-group">
                                      {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                      {!! Form::input('hidden','campus_program_id',$campus_program->id) !!}
                                      {!! Form::input('hidden','program_module_assignment_id',$assignment->id) !!}
                                    </div>
                                  </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                      </div>
                                {!! Form::close() !!}

                            </div>
                            <div class="modal-footer justify-content-between">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                          </div>
                          <!-- /.modal-content -->
                        </div>
                        <!-- /.modal-dialog -->
                      </div>
                      <!-- /.modal -->
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
                <h3 class="card-title">{{ __('No Program Module Assigned') }}</h3>
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
