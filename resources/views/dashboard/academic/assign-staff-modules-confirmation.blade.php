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
            <h1>{{ __('Module Assignment Requests') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Module Assignment Requests') }}</li>
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
                 {!! Form::open(['url'=>'academic/module-assignment/confirmation','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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

            <div class="card card-default">
           <div class="card-header">
                <ul class="nav nav-tabs">
                  @can('view-module-assignments')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Module Assignments') }}</a></li>
                  @endcan
                  @can('view-module-assignment-requests')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignment-requests?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Modules Assignment Requests') }}</a></li>
                  @endcan
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/module-assignment/confirmation?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Modules Assignment Confirmation') }}</a></li>
                  @can('view-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/modules') }}">{{ __('Modules') }}</a></li>
                  @endcan
                </ul>
              </div>
            </div>


            @if(count($assignments) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Module Assignment Requests - {{ $staff->department->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
          
                      
                      <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>Module</th>
                            <th>Programme</th>
                            <th>Code</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                          <tbody>  
                      @foreach($assignments as $assign)

                        <tr>
                        <td>{{ $assign->module->name }}
                          <p class="ss-font-xs ss-no-margin ss-bold">Requested By:</p>
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $assign->staff->title }} {{ $assign->staff->first_name }} {{ $assign->staff->middle_name }} {{ $assign->staff->surname }} - {{ $assign->staff->campus->name }}</p>
                          @if($assign->programModuleAssignment)
                          @if(count($assign->programModuleAssignment->moduleAssignments) != 0)
                            
                            <p class="ss-font-xs ss-no-margin ss-bold">Facilitator:</p>
                            @foreach($assign->programModuleAssignment->moduleAssignments as $modAssign)
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $modAssign->staff->title }} {{ $modAssign->staff->first_name }} {{ $modAssign->staff->middle_name }} {{ $modAssign->staff->surname }}

                            <a href="{{ url('academic/module-assignment/'.$assign->id.'/confirmation/accept') }}" class="ss-color-success ss-right">Accept</a></p>

                            <a href="{{ url('academic/module-assignment/'.$assign->id.'/confirmation/reject') }}" class="ss-color-danger ss-right">Reject</a></p>
                            
                            @can('delete-module-facilitator')
                            <a href="#" data-toggle="modal" data-target="#ss-delete-module-assignment-{{ $modAssign->id }}" class="ss-color-danger ss-right">Remove</a></p>
                            @endcan

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
                          @endif
                        </td>
                        <td>{{ $assign->programModuleAssignment->campusProgram->program->name }}</td>
                        <td>{{ $assign->module->code }}</td>
                        <td>{{ $assign->programModuleAssignment->year_of_study }}</td>
                        <td>{{ $assign->programModuleAssignment->semester->name }}</td>
                        <td>
                          @can('assign-module-facilitator')
                          <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-assign-module-{{ $assign->id }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign Facilitator
                         </a>
                         @endcan

                         <div class="modal fade" id="ss-assign-module-{{ $assign->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Assign Facilitator</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">

                                {!! Form::open(['url'=>'academic/module-assignment/store','class'=>'ss-form-processing']) !!}
                                   
                                   <div class="row">
                                    <div class="form-group col-12">
                                      {!! Form::label('','Select facilitator') !!}<br>
                                      <select name="staff_id" class="form-control ss-select-tags" required style="width: 100%;">
                                        <option value="">Select Facilitator</option>
                                        @foreach($staffs as $stf)
                                        <option value="{{ $stf->id }}">{{ $stf->title }} {{ $stf->first_name }} {{ $stf->surname }} @if($stf->designation) - {{ $stf->designation->name }} @endif @if($stf->campus) ({{ $stf->campus->name }}) @endif</option>
                                        @endforeach
                                      </select>

                                      {!! Form::input('hidden','module_id',$assign->module_id) !!}
                                      {!! Form::input('hidden','study_academic_year_id',$assign->study_academic_year_id) !!}
                                      {!! Form::input('hidden','program_module_assignment_id',$assign->program_module_assignment_id) !!}
                                    </div>
                                      

                                  </div>
                                      <div class="ss-form-actions">
                                       <button type="submit" class="btn btn-primary">{{ __('Assign Facilitator') }}</button>
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
                <h3 class="card-title">{{ __('No Module Assignment Requests Created') }}</h3>
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

