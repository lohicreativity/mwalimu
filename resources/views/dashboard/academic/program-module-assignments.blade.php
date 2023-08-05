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
            <h1>{{ __('Programme Modules') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programme Modules') }}</li>
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
                    <li class="nav-item"><a class="nav-link active" href="{{ url('academic/program-module-assignments?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Programme Modules') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('academic/program-module-assignment-requests?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Programme Module Requests') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/program-module-assignments','class'=>'ss-form-processing','method'=>'GET']) !!}
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
                   @if(Auth::user()->hasRole('administrator'))
                   <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                   @else
                   <div class="form-group col-6">
                    {!! Form::label('','Select campus') !!}
                    <select name="campus_id" class="form-control" required>
                       <option value="">Select Campus</option>
                       @foreach($campuses as $cp)
                       <option value="{{ $cp->id }}" @if($cp->id == $request->get('campus_id')) selected="selected" @else disabled="disabled" @endif>{{ $cp->name }}</option>
                       @endforeach
                    </select>
                  </div>
                   @endif
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
                <h3 class="card-title">{{ $campus->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Code</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($campus->campusPrograms as $program)
                  @if(App\Utils\Util::collectionContainsKey($program->program->departments,$staff->department_id))
                  <tr>
                    <td>{{ $program->program->name }}</td>
                    <td>{{ $program->code }}</td>
                    <td>
                      @can('view-programme-modules')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-view-modules-{{ $program->id }}">
                              <i class="fas fa-list-alt">
                              </i>
                              View Modules
                       </a>
                       @endcan
                       <div class="modal fade" id="ss-view-modules-{{ $program->id }}">
                        <div class="modal-dialog modal-xl">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Modules - {{ $program->program->name }}</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @if(count($program->programModuleAssignments) != 0)
                                
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
                                    <!-- <th>Special Exams</th> -->
                                    <!-- <th>Examination Irregularity</th> -->
                                  </tr>
                                  </thead>
                                  <tbody>
                                  @foreach($program->programModuleAssignments as $assignment)
                                  <tr>
                                    <td>{{ $assignment->module->name }}</td>
                                    <td>{{ $assignment->module->code }}</td>
                                    <td>{{ $assignment->year_of_study }}</td>
                                    <td>{{ $assignment->module->credit }}</td>
                                    <td>{{ $assignment->semester->name }}</td>
                                    <td>
                                      @if($assignment->category == 'OPTIONAL')
                                      <a href="{{ url('academic/student-program-module-assignment/'.$assignment->id) }}">{{ $assignment->category }}</a>
                                      @else
                                      {{ $assignment->category }}
                                      @endif
                                    </td>
                                    <td>{{ $assignment->type }}</td>
                                    <!-- <td>
                                      <a class="btn btn-info btn-sm" href="{{ url('academic/module-assignment/'.$assignment->id.'/special-exams') }}">
                              <i class="fas fa-folder-open">
                              </i>
                               Special Exams
                              </a>
                                    </td> -->
                                    <!-- <td>
                                      <a class="btn btn-info btn-sm" href="{{ url('academic/module-assignment/'.$assignment->id.'/examination-irregularities') }}">
                              <i class="fas fa-random">
                              </i>
                               Examination Irregularities
                       </a>
                                    </td> -->
                                  </tr>
                                  @endforeach
                                  
                                  </tbody>
                                </table>
                              @else
                               <h3>No module assigned.</h3>
                              @endif
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
                      @can('assign-programme-modules')
                      <a class="btn btn-info btn-sm" @if($study_academic_year->id == $request->get('study_academic_year_id')) href="{{ url('academic/program-module-assignment/'.$study_academic_year->id.'/'.$program->id.'/assign') }}" @else disabled="disabled" @endif>
                              <i class="fas fa-plus">
                              </i>
                              Assign Module
                       </a>
                      @endcan
                       
                       @if(count($program->programModuleAssignments) == 0)
                       @can('assign-programme-modules')
                       <a class="btn btn-info btn-sm" @if($study_academic_year->id == session('active_academic_year_id')) href="{{ url('academic/program-module-assignment/'.$study_academic_year->id.'/'.$program->id.'/assign-as-previous') }}" @else disabled="disabled" @endif>
                              <i class="fas fa-plus">
                              </i>
                              Assign as Previous
                       </a>
                       @endcan
                       @endif
                       
                    </td>
                  </tr>
                  @endif
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
                <h3 class="card-title">{{ __('No Programme Modules Created') }}</h3>
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
