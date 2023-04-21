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
                  <div class="row">
                   <div class="form-group col-3">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                   </div>
                   <div class="form-group col-3">
                    <select name="semester_id" class="form-control" required>
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}" @if($semester->status == 'ACTIVE') selected="selected" @endif>{{ $semester->name }}</option>
                       @endforeach
                    </select>
                   </div>
                   <div class="form-group col-4">
                    <select name="campus_program_id" class="form-control" required>
                       <option value="">Select Programme</option>
                       @foreach($campus_programs as $prog)
                         @if(Auth::user()->hasRole('hod'))
                         @if($staff->campus_id == $prog->campus_id && App\Utils\Util::collectionContainsKey($prog->program->departments,$staff->department_id))
                         <option value="{{ $prog->id }}">{{ $prog->program->name }} - {{ $prog->program->code }} - {{ $prog->campus->name }}</option>
                         @endif
                         @else
                         <option value="{{ $prog->id }}">{{ $prog->program->name }} - {{ $prog->program->code }} - {{ $prog->campus->name }}</option>
                         @endif
                       @endforeach
                    </select>
                   </div>
                   
                   <div class="form-group col-2">
                    <select name="year_of_study" class="form-control" required>
                       <option value="">Select Year of Study</option>
                       <option value="1">1</option>
                       <option value="2">2</option>
                       <option value="3">3</option>
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

           <div class="card card-default">
              <div class="card-header">
                <ul class="nav nav-tabs">       
                  @can('view-module-assignments')
                  <li class="nav-item"><a class="nav-link active" href="{{ url('academic/module-assignments?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Module Assignments') }}</a></li>
                  @endcan
                  @can('view-module-assignment-requests')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignment-requests?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Modules Assignment Requests') }}</a></li>
                  @endcan
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/module-assignment/confirmation?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Modules Assignment Confirmation') }}</a></li>
                  @can('view-modules')
                  <li class="nav-item"><a class="nav-link" href="{{ url('academic/modules') }}">{{ __('Modules') }}</a></li>
                  @endcan
                </ul>
              </div>
            </div>


            @if($study_academic_year && $campus_program)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ $campus_program->program->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
          
                      @if(count($campus_program->programModuleAssignments) != 0)
                      <table class="table table-bordered">
                        <thead>
                          <tr>
                            <th>Module</th>
                            <th>Code</th>
                            <th>Year</th>
                            <th>Semester</th>
                            <th>Previous Facilitator</th>
                            <th>Action</th>
                          </tr>
                          </thead>
                          <tbody>  
                      @foreach($campus_program->programModuleAssignments as $assign)
                        @for($i = 1; $i<=3; $i++)
							@if($i == $assign->year_of_study)
								@for($j = 1; $j<=2; $j++)
									@if($j == $assign->semester->id)
                        <tr>
                        <td>{{ $assign->module->name }}
                          @if(count($assign->module->moduleAssignments) != 0)
							  
                            @foreach($assign->module->moduleAssignments as $modAssign)
							  @if($module_assignment_requests)
								  @foreach($module_assignment_requests as $request)
									@if($request->module_id == $assign->module->id)
										Hello
									@endif
								  @endforeach
								  hello
							  @endif
                              @if($modAssign->program_module_assignment_id == $assign->id)
                            <p class="ss-font-xs ss-no-margin ss-bold">Facilitator:</p>
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $modAssign->staff->title }} {{ $modAssign->staff->first_name }} {{ $modAssign->staff->middle_name }} {{ $modAssign->staff->surname }}
                            
                            @can('delete-module-facilitator')
                            @if(App\Utils\Util::collectionContainsKey($assign->module->departments,$staff->department_id))
                            <a href="#" data-toggle="modal" data-target="#ss-delete-module-assignment-{{ $modAssign->id }}" class="ss-color-danger ss-right">Remove</a></p>
                            @endif
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $modAssign->staff->phone }}, {{ $modAssign->staff->email }} @if($modAssign->confirmed === 0) <span class="badge badge-warning">Rejected</span> @elseif($modAssign->confirmed === null) <span class="badge badge-warning">Pending Approval</span> @endif</p>
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
                              @endif
                            @endforeach
                          @endif
                        </td>
                        <td>{{ $assign->module->code }}</td>
                        <td>{{ $assign->year_of_study }}</td>
                        <td>{{ $assign->semester->name }}</td>
                        <td>
                           @php
                             $text = 'Not Available';
                           @endphp
                           
                           @foreach($previous_campus_program->programModuleAssignments as $k=>$asg)
                             @if($asg->id == $assign->id)
                                   @foreach($asg->module->moduleAssignments as $key=>$mdAsg)
                                      @if($key == 0)
                                      @php
                                       $text = $mdAsg->staff->title.' '.$mdAsg->staff->first_name.' '.$mdAsg->staff->middle_name.' '.$mdAsg->staff->surname;
                                       @endphp
                                      @endif
                                   @endforeach
                             @endif
                           @endforeach
                           {{ $text }}
                        </td>
                        <td>
                          @if(App\Utils\Util::collectionContainsKey($assign->module->departments,$staff->department_id))
                          @can('assign-module-facilitator')
                          <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-assign-module-{{ $assign->module->id }}">
                              <i class="fas fa-plus">
                              </i>
                              Assign Facilitator
                         </a>
                         @endcan
                         @else
                         {!! Form::open(['url'=>'academic/module-assignment-request/store','class'=>'ss-form-processing']) !!}
                           {!! Form::input('hidden','program_module_assignment_id',$assign->id) !!}

                           {!! Form::input('hidden','department_id',$staff->department_id) !!}

                           {!! Form::input('hidden','study_academic_year_id',$assign->study_academic_year_id) !!}

                           {!! Form::input('hidden','module_id',$assign->module->id) !!}

                           {!! Form::input('hidden','campus_program_id',$assign->campus_program_id) !!}

                         @can('request-module-facilitator')
                         <button type="submit" class="btn btn-info btn-sm" href="">
                              <i class="fas fa-plus">
                              </i>
                              Request Facilitator
                         </button>
                         @endcan
                         {!! Form::close() !!}
                         @endif

                         <div class="modal fade" id="ss-assign-module-{{ $assign->module->id }}">
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
                      
                                        <option value="{{ $stf->id }}">{{ $stf->title }} {{ $stf->first_name }} {{ $stf->surname }} - {{ $stf->designation->name }} ({{ $stf->campus->name }} - {{ $stf->department->name }})</option>
                                        
                                        @endforeach
                                      </select>

                                      {!! Form::input('hidden','module_id',$assign->module->id) !!}
                                      {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                      {!! Form::input('hidden','program_module_assignment_id',$assign->id) !!}
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
                        @endif
                        @endfor

                        @endif
                        @endfor
                      @endforeach
                    </tbody>
                    </table>
                    @else
                    <p>No Staff Module Assignment Created.</p>
                    @endif
                    
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
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
