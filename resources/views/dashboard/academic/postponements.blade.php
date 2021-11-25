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
            <h1>{{ __('Postponements') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Postponements') }}</li>
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
                 {!! Form::open(['url'=>'academic/postponements','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
                <h3 class="card-title">Add Postponement - {{ $student->first_name }} {{ $student->middle_name }} {{ $student->surname }}</h3>
              </div>
              <!-- /.card-header -->
                 {!! Form::open(['url'=>'academic/postponement/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">

                 
                 <div class="row">
                    <div class="form-group col-6">
                      {!! Form::label('','Semester') !!}
                      <select name="semester_id" class="form-control">
                         <option value="0">Select Semester</option>
                         @foreach($semesters as $semester)
                         <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                         @endforeach
                      </select>
                    </div>
                    <div class="form-group col-6">
                      {!! Form::label('','Study academic year') !!}
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}">{{ $year->academicYear->year }}</option>
                       @endforeach
                    </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-6">
                     {!! Form::label('','Category') !!}
                     <select name="category" class="form-control" required>
                       <option>Select Category</option>
                       <option value="YEAR">Year</option>
                       <option value="SEMESTER">Semester</option>
                     </select>

                     {!! Form::input('hidden','student_id',$student->id) !!}
                  </div>
                  <div class="form-group col-6">
                     {!! Form::label('','Status') !!}
                     <select name="status" class="form-control" required>
                       <option>Select Category</option>
                       <option value="POSTPONED">Postponed</option>
                       <option value="RESUMED">Resumed</option>
                     </select>
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Postponement') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endif

            @if(count($postponements) != 0)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Postponements</h3>
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
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($postponements as $post)
                  <tr>
                    <td>{{ $post->student->first_name }} {{ $post->student->middle_name }} {{ $post->student->surname }}</td>
                    <td>{{ $post->student->registration_number }}</td>
                    <td>{{ $post->studyAcademicYear->academicYear->year }}</td>
                    <td>@if($post->semester) {{ $post->semester->name }} @endif</td>
                    <td>{{ $post->category }}</td>
                    <td>{{ $post->status }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-post-{{ $post->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       <div class="modal fade" id="ss-edit-post-{{ $post->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Elective Policy</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               {!! Form::open(['url'=>'academic/elective-policy/update','class'=>'ss-form-processing']) !!}

                               <div class="row">
                                <div class="form-group col-6">
                                  {!! Form::label('','Semester') !!}
                                  <select name="semester_id" class="form-control">
                                     <option value="0">Select Semester</option>
                                     @foreach($semesters as $semester)
                                     <option value="{{ $semester->id }}" @if($semester->id == $post->semester_id) selected="selected" @endif>{{ $semester->name }}</option>
                                     @endforeach
                                  </select>
                                </div>
                                <div class="form-group col-6">
                                  {!! Form::label('','Study academic year') !!}
                                <select name="study_academic_year_id" class="form-control" required>
                                   <option value="">Select Study Academic Year</option>
                                   @foreach($study_academic_years as $year)
                                   <option value="{{ $year->id }}" @if($year->id == $post->study_academic_year_id) selected="selected" @endif>{{ $year->academicYear->year }}</option>
                                   @endforeach
                                </select>
                              </div>
                             </div>
                             <div class="row">
                              <div class="form-group col-6">
                                 {!! Form::label('','Category') !!}
                                 <select name="category" class="form-control" required>
                                   <option>Select Category</option>
                                   <option value="YEAR">Year</option>
                                   <option value="SEMESTER">Semester</option>
                                 </select>

                                 {!! Form::input('hidden','student_id',$post->student_id) !!}

                                 {!! Form::input('hidden','postponement_id',$post->id) !!}
                              </div>
                              <div class="form-group col-6">
                                 {!! Form::label('','Status') !!}
                                 <select name="status" class="form-control" required>
                                   <option>Select Category</option>
                                   <option value="POSTPONED" @if($post->category == 'POSTPONED') selected="selected" @endif>Postponed</option>
                                   <option value="RESUMED" @if($post->category == 'RESUMED') selected="selected" @endif>Resumed</option>
                                 </select>
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

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-post-{{ $post->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

                       <div class="modal fade" id="ss-delete-post-{{ $post->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this postponement from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Abort</button>
                                         <a href="{{ url('academic/postponement/'.$post->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Postponement Created') }}</h3>
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
