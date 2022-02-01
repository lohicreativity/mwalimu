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
            <h1>{{ __('Programme Module Assignment Requests') }} - {{ $study_academic_year->academicYear->year }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programme Module Assignment Requests') }}</li>
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
                    <li class="nav-item"><a class="nav-link" href="{{ url('academic/program-module-assignments?study_academic_year_id='.session('active_academic_year_id').'&campus_id='.session('staff_campus_id')) }}">{{ __('Programme Modules') }}</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ url('academic/program-module-assignment-requests?study_academic_year_id='.session('active_academic_year_id')) }}">{{ __('Programme Module Requests') }}</a></li>
                </ul>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/program-module-assignment-requests','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
                   <div class="form-group">
                    <select name="study_academic_year_id" class="form-control" required>
                       <option value="">Select Study Academic Year</option>
                       @foreach($study_academic_years as $year)
                       <option value="{{ $year->id }}" @if($year->status == 'ACTIVE') selected="selected" @endif>{{ $year->academicYear->year }}</option>
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

            @if(count($requests) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Programme Modules Requests') }} - {{ $staff->department->name }} - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Programme</th>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Year</th>
                    <th>Credits</th>
                    <th>Semester</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($requests as $request)
                  <tr>
                    <td>{{ $request->programModuleAssignment->campusProgram->program->name }}
                    <p class="ss-font-xs ss-no-margin ss-bold">Requested By:</p>
                            <p class="ss-font-xs ss-no-margin ss-italic">{{ $request->staff->title }} {{ $request->staff->first_name }} {{ $request->staff->middle_name }} {{ $request->staff->surname }} - {{ $request->staff->campus->name }}</p>
                    </td>
                    <td>{{ $request->programModuleAssignment->module->name }}</td>
                    <td>{{ $request->programModuleAssignment->module->code }}</td>
                    <td>{{ $request->programModuleAssignment->year_of_study }}</td>
                    <td>{{ $request->programModuleAssignment->module->credit }}</td>
                    <td>{{ $request->programModuleAssignment->semester->name }}</td>
                    <td>{{ $request->programModuleAssignment->category }}</td>
                    <td>{{ $request->programModuleAssignment->type }}</td>
                    <td>@if($request->is_ready == 1)
                          <span class="badge badge-success">Attended</span>
                        @else
                          <span class="badge badge-warning">Not Attended</span>
                        @endif
                    </td>
                    <td>
                      @can('edit-programme-module-assignment')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-module-assignment-{{ $request->programModuleAssignment->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-module-assignment-{{ $request->programModuleAssignment->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title">Edit Programme Module</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                  $year_of_study = [
                                     'placeholder'=>'Year of study',
                                     'class'=>'form-control',
                                     'required'=>true
                                  ];

                                  $course_work_min_mark = [
                                     'class'=>'form-control ss-course-work-min-mark',
                                     'placeholder'=>'Course work min mark',
                                     'data-target'=>'#ss-final-min-mark-'.$request->programModuleAssignment->id,
                                     'id'=>'ss-course-work-min-mark-'.$request->programModuleAssignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_percentage_pass = [
                                     'class'=>'form-control ss-course-work-percentage-pass',
                                     'placeholder'=>'Course work percentage pass',
                                     'data-from'=>'#ss-course-work-min-mark-'.$request->programModuleAssignment->id,
                                     'data-target'=>'#ss-course-work-pass-score-'.$request->programModuleAssignment->id,
                                     'id'=>'ss-course-work-percentage-pass-'.$request->programModuleAssignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_pass_score = [
                                     'class'=>'form-control ss-course-work-pass-score',
                                     'placeholder'=>'Course work pass score',
                                     'id'=>'ss-course-work-pass-score-'.$request->programModuleAssignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_min_mark = [
                                     'class'=>'form-control ss-final-min-mark',
                                     'placeholder'=>'Final min mark',
                                     'id'=>'ss-final-min-mark-'.$request->programModuleAssignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_percentage_pass = [
                                     'class'=>'form-control ss-final-percentage-pass',
                                     'placeholder'=>'Final percentage pass',
                                     'data-from'=>'#ss-final-min-mark-'.$request->programModuleAssignment->id,
                                     'data-target'=>'#ss-final-pass-score-'.$request->programModuleAssignment->id,
                                     'id'=>'ss-final-percentage-pass-'.$request->programModuleAssignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_pass_score = [
                                     'class'=>'form-control ss-final-pass-score',
                                     'placeholder'=>'Final pass score',
                                     'id'=>'ss-final-pass-score-'.$request->programModuleAssignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $module_pass_mark = [
                                     'class'=>'form-control ss-module-pass-mark',
                                     'placeholder'=>'Module pass mark',
                                     'id'=>'ss-module-pass-mark-'.$request->programModuleAssignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];
                               @endphp
                                {!! Form::open(['url'=>'academic/program-module-assignment/update','class'=>'ss-form-processing']) !!}

                                   <div class="row">
                                     <div class="form-group col-8">
                                      {!! Form::label('','Module') !!}<br>
                                      <select name="module_id" class="form-control ss-select-tags ss-select-module" required style="width: 100%;" data-year-target="#ss-year-{{ $request->programModuleAssignment->module_id }}" data-semester-target="#ss-semester-{{ $request->programModuleAssignment->module_id }}" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-module-by-id') }}">
                                         <option value="">Select Module</option>
                                         @foreach($inclusive_modules as $module)
                                         <option value="{{ $module->id }}" @if($request->programModuleAssignment->module_id == $module->id) selected="selected" @else disabled="disabled" @endif>{{ $module->name }} - {{ $module->code }}</option>
                                         @endforeach
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Year of study') !!}
                                        <select name="year_of_study" class="form-control" required id="ss-year-{{ $module->id }}">
                                          @for($i = 1; $i <= $request->programModuleAssignment->campusProgram->program->min_duration; $i++)
                                          <option value="{{ $i }}" @if($i == $request->programModuleAssignment->year_of_study) selected="selected" @else disabled="disabled" @endif>{{ $i }}</option>
                                          @endfor
                                        </select>
                                      </div>
                                    </div>
                                      <div class="row">
                                      <div class="form-group col-4">
                                      {!! Form::label('','Category') !!}
                                      <select name="category" class="form-control" required>
                                         <option value="COMPULSORY" @if($request->programModuleAssignment->category == 'COMPULSORY') selected="selected" @else disabled="disabled" @endif>Compulsory</option>
                                         <option value="OPTIONAL" @if($request->programModuleAssignment->category == 'OPTIONAL') selected="selected" @else disabled="disabled" @endif>Optional</option>
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                      {!! Form::label('','Type') !!}
                                      <select name="type" class="form-control" required>
                                         <option value="CORE" @if($request->programModuleAssignment->type == 'CORE') selected="selected" @else disabled="disabled" @endif>Core</option>
                                         <option value="FUNDAMENTAL" @if($request->programModuleAssignment->type == 'FUNDAMENTAL') selected="selected" @else disabled="disabled" @endif>Fundamental</option>
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                      {!! Form::label('','Semester') !!}
                                      <select name="semester_id" class="form-control" required id="ss-semester-{{ $module->id }}">
                                         <option value="">Select Semester</option>
                                         @foreach($semesters as $semester)
                                         <option value="{{ $semester->id }}" @if($request->programModuleAssignment->semester_id == $semester->id) selected="selected" @else disabled="disabled" @endif>{{ $semester->name }}</option>
                                         @endforeach
                                      </select>
                                      </div>
                                          {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                          {!! Form::input('hidden','campus_program_id',$request->programModuleAssignment->campus_program_id) !!}
                                          {!! Form::input('hidden','program_module_assignment_id',$request->programModuleAssignment->id) !!}

                                      
                                    </div>
                                    <div class="row">
                                <div class="form-group col-4">
                                  {!! Form::label('','Coursework max mark') !!}
                                  {!! Form::input('number','course_work_min_mark',null,$course_work_min_mark) !!}
                                </div>
                                <div class="form-group col-4">
                                  {!! Form::label('','Coursework percentage pass') !!}
                                  {!! Form::input('number','course_work_percentage_pass',null,$course_work_percentage_pass) !!}
                                </div>
                                <div class="form-group col-4">
                                 {!! Form::label('','Coursework pass score') !!}
                                 {!! Form::input('number','course_work_pass_score',null,$course_work_pass_score) !!}

                                 {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                              </div>
                             </div>
                             <div class="row">
                              
                              <div class="form-group col-6">
                                  {!! Form::label('','Final max mark') !!}
                                  {!! Form::input('number','final_min_mark',null,$final_min_mark) !!}
                              </div>
                              <div class="form-group col-6">
                                  {!! Form::label('','Final percentage pass') !!}
                                  {!! Form::input('number','final_percentage_pass',null,$final_percentage_pass) !!}
                              </div>
                            </div>
                            <div class="row">
                               <div class="form-group col-6">
                                  {!! Form::label('','Final pass score') !!}
                                  {!! Form::input('number','final_pass_score',null,$final_pass_score) !!}
                              </div>
                              <div class="form-group col-6">
                                  {!! Form::label('','Module pass mark') !!}
                                  {!! Form::input('number','module_pass_mark',null,$module_pass_mark) !!}
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
                <h3 class="card-title">{{ __('No Module Assignment Requested') }}</h3>
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
