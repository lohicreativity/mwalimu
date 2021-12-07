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
            <h1>{{ __('Staff Module Assignment') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Staff Module Assignment') }}</li>
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
                 {!! Form::open(['url'=>'academic/module-assignments','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
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




            @if(count($campuses) != 0 && $study_academic_year)
            @foreach($campuses as $campus)
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
                    <th>Modules</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($campus->campusPrograms as $program)
                  <tr>
                    <td>{{ $program->program->name }}</td>
                    <td>{{ $program->program->code }}</td>
                    <td>
                      @if(count($program->programModuleAssignments) != 0)
                      <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>Module</th>
                            <th>Code</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                          <tbody>  
                      @foreach($program->programModuleAssignments as $assign)
                        @for($i = 1; $i<=3; $i++)
                        @if($i == $assign->year_of_study)

                        @for($j = 1; $j<=2; $j++)
                        @if($j == $assign->semester->id)
                        <tr>
                        <td>{{ $assign->module->name }}
                          @if(count($assign->module->moduleAssignments) != 0)
                            <p class="ss-font-xs ss-no-margin ss-bold">Facilitator:</p>
                            @foreach($assign->module->moduleAssignments as $modAssign)
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $modAssign->staff->title }} {{ $modAssign->staff->first_name }} {{ $modAssign->staff->middle_name }} {{ $modAssign->staff->surname }} - {{ $modAssign->category }}<a href="#" data-toggle="modal" data-target="#ss-delete-module-assignment-{{ $modAssign->id }}" class="ss-color-danger ss-right">Remove</a></p>

                            <div class="modal fade" id="ss-delete-module-assignment-{{ $modAssign->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Confirmation Alert</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-12">
                                    <div id="ss-confirmation-container">
                                       <p id="ss-confirmation-text">Are you sure you want to delete this module assignment from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/module-assignment/'.$modAssign->id.'/destroy') }}" class="btn btn-danger">Delete</a>
                                         </div><!-- end of ss-form-controls -->
                                      </div><!-- end of ss-confirmation-container -->
                                  </div><!-- end of col-md-12 -->
                               </div><!-- end of row -->
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

                            @endforeach
                          @endif
                        </td>
                        <td>{{ $assign->module->code }}</td>
                        <td>{{ $assign->year_of_study }}</td>
                        <td>{{ $assign->semester->name }}</td>
                        <td>
                          <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-assign-module-{{ $assign->module->id }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign Staff
                         </a>

                         <div class="modal fade" id="ss-assign-module-{{ $assign->module->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Assign Staff</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                {!! Form::open(['url'=>'academic/module-assignment/store','class'=>'ss-form-processing']) !!}
                                   
                                   <div class="row">
                                    <div class="form-group col-8">
                                      {!! Form::label('','Select staff') !!}
                                      <select name="staff_id" class="form-control" required>
                                        <option value="">Select Staff</option>
                                        @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->title }} {{ $staff->first_name }} {{ $staff->surname }} - {{ $staff->designation->name }}</option>
                                        @endforeach
                                      </select>

                                      {!! Form::input('hidden','module_id',$assign->module->id) !!}
                                      {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                      {!! Form::input('hidden','program_module_assignment_id',$assign->id) !!}
                                    </div>
                                      <div class="form-group col-4">
                                      {!! Form::label('','Category') !!}
                                      <select name="category" class="form-control" required>
                                         <option value="Lead Facilitator">Lead Facilitator</option>
                                         <option value="Assistant Facilitator">Assistant Facilitator</option>
                                         <option value="Tutor">Tutor</option>
                                      </select>
                                      </div>

                                  </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Assign Staff') }}</button>
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
                        @endif
                        @endfor

                        @endif
                        @endfor
                      @endforeach
                    </tbody>
                    </table>
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
            @endforeach
            @else
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('No Campus Programs Created') }}</h3>
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
