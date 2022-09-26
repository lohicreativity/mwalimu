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
            <h1>{{ __('Examination Irregularity') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Examination Irregularity') }}</li>
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
                <h3 class="card-title">{{ __('Search for student') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                     $reg_number = [
                         'class'=>'form-control',
                         'placeholder'=>'Registration number',
                         'required'=>true
                     ];
                 @endphp 
                 {!! Form::open(['url'=>'academic/module-assignment/'.$module_assignment->id.'/examination-irregularities','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group col-6">
                    {!! Form::label('','Enter student registration number') !!}
                    {!! Form::text('registration_number',null,$reg_number) !!}
                     
                  </div>
                  <div class="ss-form-actions">
                   <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                  </div>

                 {!! Form::close() !!}
                  
              </div>
            </div>
            <!-- /.card -->

            @if($student)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Add Examination Irregularity - {{ $module_assignment->module->name }} ({{ $module_assignment->module->code }}) - {{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }} (Reg# {{ $student->registration_number }})</h3>
              </div>
              <!-- /.card-header -->
                @php
                    $description = [
                       'class'=>'form-control',
                       'placeholder'=>'Description',
                       'rows'=>2,
                       'required'=>true
                    ];
                 @endphp
                 {!! Form::open(['url'=>'academic/examination-irregularity/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 

                 <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Semester') !!}
                      <select name="semester" class="form-control" disabled="disabled">
                         <option value="">Select Semester</option>
                         @foreach($semesters as $semester)
                         <option value="{{ $semester->id }}" @if($module_assignment->programModuleAssignment->semester_id == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                         @endforeach
                      </select>
                      {!! Form::input('hidden','semester_id',$module_assignment->programModuleAssignment->semester_id) !!}
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year" class="form-control" disabled="disabled">
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($module_assignment->study_academic_year_id == $year->id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                    {!! Form::input('hidden','study_academic_year_id',$module_assignment->study_academic_year_id) !!}
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-12">
                     {!! Form::label('','Description') !!}
                     {!! Form::textarea('description',null,$description) !!}

                     {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}
                     {!! Form::input('hidden','student_id',$student->id) !!}
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Examination Irregularity') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endif

            @if(count($irregularities) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Examination Irregularities - {{ $module_assignment->module->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Student</th>
                    <th>Reg Number</th>
                    <th>Study Academic Year</th>
                    <th>Semester</th>
                    <th>Module</th>
                    <th>Description</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($irregularities as $irregularity)
                  <tr>
                    <td>{{ $irregularity->student->first_name }} {{ $irregularity->student->middle_name }} {{ $irregularity->student->surname }}</td>
                    <td>{{ $irregularity->student->registration_number }}</td>
                    <td>{{ $irregularity->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $irregularity->semester->name }}</td>
                    <td>{{ $irregularity->moduleAssignment->module->name }} - {{ $irregularity->moduleAssignment->module->code }}</td>
                    <td>{{ $irregularity->description }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-irregularity-{{ $irregularity->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       <div class="modal fade" id="ss-edit-irregularity-{{ $irregularity->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Examination Irregularity</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               @php
                                  $description = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Description',
                                     'rows'=>2,
                                     'required'=>true
                                  ];
                               @endphp
                               {!! Form::open(['url'=>'academic/examination-irregularity/update','class'=>'ss-form-processing']) !!}

                              <div class="row">
                              <div class="form-group col-6">
                                {!! Form::label('','Semester') !!}
                                <select name="semester_id" class="form-control" disabled="disabled">
                                   <option value="">Select Semester</option>
                                   @foreach($semesters as $semester)
                                   <option value="{{ $semester->id }}" @if($irregularity->semester_id == $semester->id) selected="selected" @endif>{{ $semester->name }}</option>
                                   @endforeach
                                </select>
                                {!! Form::input('hidden','semester_id',$irregularity->semester_id) !!}
                              </div>
                              <div class="form-group col-6">
                                {!! Form::label('','Study academic year') !!}
                              <select name="study_academic_year" class="form-control" disabled="disabled">
                                 <option value="">Select Study Academic Year</option>
                                 @foreach($study_academic_years as $year)
                                 <option value="{{ $year->id }}" @if($year->id == $irregularity->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                 @endforeach
                              </select> 
                              {!! Form::input('hidden','study_academic_year_id',$irregularity->study_academic_year_id) !!}
                            </div>
                           </div>
                           <div class="row">
                            <div class="form-group col-12">
                               {!! Form::label('','Description') !!}
                               {!! Form::textarea('description',$irregularity->description,$description) !!}
                               
                               {!! Form::input('hidden','module_assignment_id',$module_assignment->id) !!}

                               {!! Form::input('hidden','student_id',$irregularity->student_id) !!}

                               {!! Form::input('hidden','examination_irregularity_id',$irregularity->id) !!}
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

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-irregularity-{{ $irregularity->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-irregularity-{{ $irregularity->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this examination irregularity from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/examination-irregularity/'.$irregularity->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Examination Irregularity Created') }}</h3>
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
