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
            <h1>{{ __('Elective Policies') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Elective Policies') }}</li>
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
                 {!! Form::open(['url'=>'academic/elective-policies','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
             
            @if($study_academic_year)
            @can('add-elective-policy')
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Add Elective Policy - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
                @php
                    $number_of_options = [
                       'class'=>'form-control',
                       'placeholder'=>'Number of options',
                       'required'=>true
                    ];
                 @endphp
                 {!! Form::open(['url'=>'academic/elective-policy/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 

                 <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Programme') !!}
                      <select name="campus_program_id" class="form-control" required>
                         <option value="">Select Campus Programme</option>
                         @foreach($campus_programs as $prog)
                         @if(Auth::user()->hasRole('hod'))
                         @if($staff->campus_id == $prog->campus_id && $staff->department_id == $prog->program->department_id)
                         <option value="{{ $prog->id }}">{{ $prog->program->name }} - {{ $prog->campus->name }}</option>
                         @endif
                         @else
                         <option value="{{ $prog->id }}">{{ $prog->program->name }} - {{ $prog->campus->name }}</option>
                         @endif
                         @endforeach
                      </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Semester') !!}
                      <select name="semester_id" class="form-control" required>
                         <option value="">Select Semester</option>
                         @foreach($semesters as $semester)
                         <option value="{{ $semester->id }}" @if($semester->id == session('active_semester_id')) selected="selected" @endif>{{ $semester->name }}</option>
                         @endforeach
                      </select>
                    </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                   {!! Form::label('','Year of study') !!}
                   <select name="year_of_study" class="form-control" required>
                     <option value="">Select Year of Study</option>
                     <option value="1">1</option>
                     <option value="2">2</option>
                     <option value="3">3</option>
                   </select>
                </div>
                  <div class="form-group col-6">
                     {!! Form::label('','Number of options allowed') !!}
                     {!! Form::input('number','number_of_options',null,$number_of_options) !!}

                     {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Elective Policy') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan
            @endif

            @if(count($elective_policies) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Elective Policies - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Campus</th>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Year</th>
                    <th>Options</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($elective_policies as $policy)
                  <tr>
                    <td>{{ $policy->campusProgram->program->name }}</td>
                    <td>{{ $policy->campusProgram->campus->name }}</td>
                    <td>{{ $policy->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $policy->semester->name }}</td>
                    <td>{{ $policy->year_of_study }}</td>
                    <td>{{ $policy->number_of_options }}</td>
                    <td>
                      @can('edit-elective-policy')
                      <a class="btn btn-info btn-sm" href="#" @if($study_academic_year->id == session('active_academic_year_id')) data-toggle="modal" data-target="#ss-edit-policy-{{ $policy->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       @endcan
                       <div class="modal fade" id="ss-edit-policy-{{ $policy->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Elective Policy</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                  $number_of_options = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Number of options',
                                     'required'=>true
                                  ];
                               @endphp
                               {!! Form::open(['url'=>'academic/elective-policy/update','class'=>'ss-form-processing']) !!}

                               <div class="row">
                                  <div class="form-group col-6">
                                    {!! Form::label('','Programme') !!}
                                    <select name="campus_program_id" class="form-control" required>
                                       <option value="">Select Campus Programme</option>
                                       @foreach($campus_programs as $prog)
                                       <option value="{{ $prog->id }}" @if($prog->id == $policy->campus_program_id) selected="selected" @endif>{{ $prog->program->name }} - {{ $prog->campus->name }}</option>
                                       @endforeach
                                    </select>
                                  </div>
                                  <div class="form-group col-6">
                                    {!! Form::label('','Semester') !!}
                                    <select name="semester_id" class="form-control" required>
                                       <option value="">Select Semester</option>
                                       @foreach($semesters as $semester)
                                       <option value="{{ $semester->id }}" @if($policy->semester_id == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                                       @endforeach
                                    </select>
                                  </div>
                               </div>
                               <div class="row">
                                <div class="form-group col-6">
                                   {!! Form::label('','Year of study') !!}
                                   <select name="year_of_study" class="form-control" required>
                                     <option value="">Select Year of Study</option>
                                     <option value="1" @if($policy->year_of_study == 1) selected="selected" @endif>1</option>
                                     <option value="2" @if($policy->year_of_study == 2) selected="selected" @endif>2</option>
                                     <option value="3" @if($policy->year_of_study == 3) selected="selected" @endif>3</option>
                                   </select>
                                </div>
                                <div class="form-group col-6">
                                   {!! Form::label('','Number of options allowed') !!}
                                   {!! Form::input('number','number_of_options',$policy->number_of_options,$number_of_options) !!}

                                   {!! Form::input('hidden','study_academic_year_id',$policy->study_academic_year_id) !!}

                                   {!! Form::input('hidden','elective_policy_id',$policy->id) !!}
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
                      
                      @can('delete-elective-policy')
                      <a class="btn btn-danger btn-sm" @if($study_academic_year->id == session('active_academic_year_id')) href="#" data-toggle="modal" data-target="#ss-delete-policy-{{ $policy->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-delete-policy-{{ $policy->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this programme from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/elective-policy/'.$policy->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Elective Policy Created') }}</h3>
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
