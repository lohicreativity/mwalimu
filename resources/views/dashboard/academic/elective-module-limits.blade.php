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
            <h1>{{ __('Elective (Option) Selection Deadline') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Elective (Option) Selection Deadline') }}</li>
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
                 {!! Form::open(['url'=>'academic/elective-module-limits','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->id == $request->get('study_academic_year_id')) selected="selected" @endif>{{ $year->academicYear->year }}</option>
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
            @can('add-elective-deadline')
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Add Elective (Option) Selection Deadline - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
                @php
                    $deadline = [
                       'class'=>'form-control ss-datepicker',
                       'placeholder'=>'Deadline',
                       'autofocus'=>'off',
                       'required'=>true
                    ];
                 @endphp
                 {!! Form::open(['url'=>'academic/elective-module-limit/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 

                 <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Campus') !!}
                      <select name="campus_id" class="form-control" required>
                         <option value="">Select Campus</option>
                         @foreach($campuses as $campus)
                         <option value="{{ $campus->id }}" @if($campus->id == session('staff_campus_id')) selected="selected" @else disabled="disabled" @endif>{{ $campus->name }}</option>
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
                  {!! Form::label('','Award') !!}
                  <select name="award_id" class="form-control" required>
                     <option value="">Select Award</option>
                     @foreach($awards as $award)
                     <option value="{{ $award->id }}">{{ $award->name }}</option>
                     @endforeach
                  </select>
                </div>
                  <div class="form-group col-6">
                     {!! Form::label('','Deadline') !!}
                     {!! Form::text('deadline',null,$deadline) !!}

                     {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Elective Selection Deadline') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan
            @endif

            @if(count($elective_module_limits) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Elective (Option) Selection Deadlines - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Campus</th>
                    <th>Award</th>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Deadline</th>
                    @if(Auth::user()->hasRole('admission-officer'))
                    <th>Actions</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($elective_module_limits as $limit)
                  <tr>
                    <td>{{ $limit->campus->name }}</td>
                    <td>{{ $limit->award->name }}</td>
                    <td>{{ $limit->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $limit->semester->name }}</td>
                    <td>{{ $limit->deadline }}</td>
                    @if(Auth::user()->hasRole('admission-officer'))
                      <td>
                        @can('edit-elective-deadline')
                        <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-limit-{{ $limit->id }}">
                                <i class="fas fa-pencil-alt">
                                </i>
                                Edit
                        </a>
                        @endcan

                        <div class="modal fade" id="ss-edit-limit-{{ $limit->id }}">
                          <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Elective (Option) Selection Deadline</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body">
                                @php
                                    $deadline = [
                                      'class'=>'form-control ss-datepicker',
                                      'placeholder'=>'Deadline',
                                      'autofocus'=>'off',
                                      'required'=>true
                                    ];
                                @endphp
                                {!! Form::open(['url'=>'academic/elective-module-limit/update','class'=>'ss-form-processing']) !!}

                                <div class="row">
                                    <div class="form-group col-6">
                                      {!! Form::label('','Campus') !!}
                                      <select name="campus_id" class="form-control" required>
                                        <option value="">Select Campus</option>
                                        @foreach($campuses as $campus)
                                        <option value="{{ $campus->id }}" @if($campus->id == $limit->campus_id) selected="selected" @else disabled="disabled" @endif>{{ $campus->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                    <div class="form-group col-6">
                                      {!! Form::label('','Semester') !!}
                                      <select name="semester_id" class="form-control" required>
                                        <option value="">Select Semester</option>
                                        @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" @if($limit->semester_id == $semester->id) selected="selected" @else disabled="disabled" @endif>{{ $semester->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                </div>
                                <div class="row">
                                  <div class="form-group col-6">
                                      {!! Form::label('','Award') !!}
                                      <select name="award_id" class="form-control" required>
                                        <option value="">Select Award</option>
                                        @foreach($awards as $award)
                                        <option value="{{ $award->id }}" @if($limit->award_id == $award->id) selected="selected" @else disabled="disabled" @endif>{{ $award->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  <div class="form-group col-6">
                                    {!! Form::label('','Deadline') !!}
                                    {!! Form::text('deadline',App\Utils\DateMaker::toStandardDate($limit->deadline),$deadline) !!}

                                    {!! Form::input('hidden','study_academic_year_id',$limit->study_academic_year_id) !!}

                                    {!! Form::input('hidden','elective_module_limit_id',$limit->id) !!}
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
                        
                        @can('delete-elective-deadline')
                        <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-limit-{{ $limit->id }}">
                                <i class="fas fa-trash">
                                </i>
                                Delete
                        </a>
                        @endcan

                        <div class="modal fade" id="ss-delete-limit-{{ $limit->id }}">
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
                                        <p id="ss-confirmation-text">Are you sure you want to delete this deadline from the list?</p>
                                        <div class="ss-form-controls">
                                          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                          <a href="{{ url('academic/elective-module-limit/'.$limit->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                    @endif
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
                <h3 class="card-title">{{ __('No Elective Module Limit Created') }}</h3>
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
