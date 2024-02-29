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
            <h1>{{ __('Programme Module Assignment') }} - {{ $campus_program->program->name }} - {{ $study_academic_year->academicYear->year }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Programme Module Assignment') }}</li>
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
            
            @can('add-programme-module-assignment')
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Add Module Assignment') }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 @php
                    $year_of_study = [
                       'placeholder'=>'Year of study',
                       'class'=>'form-control',
                       'required'=>true
                    ];

                    $course_work_min_mark = [
                       'class'=>'form-control ss-course-work-min-mark',
                       'placeholder'=>'Course work min mark',
                       'data-target'=>'#ss-final-min-mark',
                       'id'=>'ss-course-work-min-mark',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $course_work_percentage_pass = [
                       'class'=>'form-control ss-course-work-percentage-pass',
                       'placeholder'=>'Course work percentage pass',
                       'data-from'=>'#ss-course-work-min-mark',
                       'data-target'=>'#ss-course-work-pass-score',
                       'id'=>'ss-course-work-percentage-pass',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $course_work_pass_score = [
                       'class'=>'form-control ss-course-work-pass-score',
                       'placeholder'=>'Course work pass score',
                       'id'=>'ss-course-work-pass-score',
                       'steps'=>'any',
                       'readonly'=>true,
                       'required'=>true
                    ];

                    $final_min_mark = [
                       'class'=>'form-control ss-final-min-mark',
                       'placeholder'=>'Final min mark',
                       'id'=>'ss-final-min-mark',
                       'steps'=>'any',
                       'readonly'=>true,
                       'required'=>true
                    ];

                    $final_percentage_pass = [
                       'class'=>'form-control ss-final-percentage-pass',
                       'placeholder'=>'Final percentage pass',
                       'data-from'=>'#ss-final-min-mark',
                       'data-target'=>'#ss-final-pass-score',
                       'id'=>'ss-final-percentage-pass',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $final_pass_score = [
                       'class'=>'form-control ss-final-pass-score',
                       'placeholder'=>'Final pass score',
                       'id'=>'ss-final-pass-score',
                       'steps'=>'any',
                       'readonly'=>true,
                       'required'=>true
                    ];

                    $module_pass_mark = [
                       'class'=>'form-control ss-module-pass-mark',
                       'placeholder'=>'Module pass mark',
                       'id'=>'ss-module-pass-mark',
                       'steps'=>'any',
                       'required'=>true
                    ];
                 @endphp

                 {!! Form::open(['url'=>'academic/program-module-assignment/store','class'=>'ss-form-processing']) !!}
                   
                   <div class="row">
                   <div class="form-group col-8">
                    {!! Form::label('','Module') !!}
                    <select name="module_id" class="form-control ss-select-tags" required data-year-target="#ss-year, #ss-year-input" data-semester-target="#ss-semester, #ss-semester-input" 
                            data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-module-by-id') }}" data-cw-min-mark-target="#ss-course-work-min-mark" 
                            data-cw-percentage-pass-target="#ss-course-work-percentage-pass" data-cw-pass-score-target="#ss-course-work-pass-score" data-final-min-mark-target="#ss-final-min-mark">
                       <option value="">Select Module</option>
                       @foreach($modules as $module)
                       <option value="{{ $module->id }}">{{ $module->name }} - {{ $module->code }} </option>
                       @endforeach
                    </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Year of study') !!}
                      <select name="year_of_study" class="form-control" required id="ss-year" disabled="disabled">
                        <option>Select Year of Study</option>
                        @for($i = 1; $i <= $campus_program->program->min_duration; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                      </select>
                      
                    </div>
                  </div>
                    <div class="row">
                    <div class="form-group col-4">
                    {!! Form::label('','Category') !!}
                    <select name="category" class="form-control" required>
                       <option value="">Select Category</option>
                       <option value="COMPULSORY">Compulsory</option>
                       <option value="OPTIONAL">Optional</option>
                    </select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Type') !!}
                    <select name="type" class="form-control" required>
                       <option value="">Select Type</option>
                       <option value="CORE">Core</option>
                       <option value="FUNDAMENTAL">Fundamental</option>
                    </select>
                    </div>
                    <div class="form-group col-4">
                    {!! Form::label('','Semester') !!}
                    <select name="semester_id" class="form-control" required id="ss-semester" disabled="disabled">
                       <option value="">Select Semester</option>
                       @foreach($semesters as $semester)
                       <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                       @endforeach
                    </select>
                    </div>
                  {!! Form::input('hidden','semester_id',null,['id'=>'ss-semester-input']) !!}
                  {!! Form::input('hidden','year_of_study',null,['id'=>'ss-year-input']) !!}
                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  {!! Form::input('hidden','campus_program_id',$campus_program->id) !!}
                    
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
                   <button type="submit" class="btn btn-primary">{{ __('Assign Module') }}</button>
                  </div>

                 {!! Form::close() !!}
              </div>
            </div>
            <!-- /.card -->
            @endcan

            @if(count($assignments) != 0 && $campus_program && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{ __('Programme Modules') }} - {{ $campus_program->program->name }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
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
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($assignments as $assignment)
                  <tr>
                    <td>{{ $assignment->module->name }}</td>
                    <td>{{ $assignment->module->code }}</td>
                    <td>{{ $assignment->year_of_study }}</td>
                    <td>{{ $assignment->module->credit }}</td>
                    <td>{{ $assignment->semester->name }}</td>
                    <td>{{ $assignment->category }}</td>
                    <td>{{ $assignment->type }}</td>
                    <td>
                      @can('edit-programme-module-assignment')
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-module-assignment-{{ $assignment->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-module-assignment-{{ $assignment->id }}">
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
                                     'data-target'=>'#ss-final-min-mark-'.$assignment->id,
                                     'id'=>'ss-course-work-min-mark-'.$assignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_percentage_pass = [
                                     'class'=>'form-control ss-course-work-percentage-pass',
                                     'placeholder'=>'Course work percentage pass',
                                     'data-from'=>'#ss-course-work-min-mark-'.$assignment->id,
                                     'data-target'=>'#ss-course-work-pass-score-'.$assignment->id,
                                     'id'=>'ss-course-work-percentage-pass-'.$assignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_pass_score = [
                                     'class'=>'form-control ss-course-work-pass-score',
                                     'placeholder'=>'Course work pass score',
                                     'id'=>'ss-course-work-pass-score-'.$assignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_min_mark = [
                                     'class'=>'form-control ss-final-min-mark',
                                     'placeholder'=>'Final min mark',
                                     'id'=>'ss-final-min-mark-'.$assignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_percentage_pass = [
                                     'class'=>'form-control ss-final-percentage-pass',
                                     'placeholder'=>'Final percentage pass',
                                     'data-from'=>'#ss-final-min-mark-'.$assignment->id,
                                     'data-target'=>'#ss-final-pass-score-'.$assignment->id,
                                     'id'=>'ss-final-percentage-pass-'.$assignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_pass_score = [
                                     'class'=>'form-control ss-final-pass-score',
                                     'placeholder'=>'Final pass score',
                                     'id'=>'ss-final-pass-score-'.$assignment->id,
                                     'readonly'=>true,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $module_pass_mark = [
                                     'class'=>'form-control ss-module-pass-mark',
                                     'placeholder'=>'Module pass mark',
                                     'id'=>'ss-module-pass-mark-'.$assignment->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];
                               @endphp
                                {!! Form::open(['url'=>'academic/program-module-assignment/update','class'=>'ss-form-processing']) !!}

                                   <div class="row">
                                     <div class="form-group col-8">
                                      {!! Form::label('','Module') !!}<br>
                                      <select name="module_id" class="form-control ss-select-tags ss-select-module" required style="width: 100%;" data-year-target="#ss-year-{{ $assignment->module_id }}" data-semester-target="#ss-semester-{{ $assignment->module_id }}" data-token="{{ session()->token() }}" data-source-url="{{ url('api/v1/get-module-by-id') }}">
                                         <option value="">Select Module</option>
                                         @foreach($inclusive_modules as $module)
                                         <option value="{{ $module->id }}" @if($assignment->module_id == $module->id) selected="selected" @else disabled="disabled" @endif>{{ $module->name }} - {{ $module->code }}</option>
                                         @endforeach
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                        {!! Form::label('','Year of study') !!}
                                        <select name="year_of_study" class="form-control" required id="ss-year-{{ $module->id }}">
                                          @for($i = 1; $i <= $campus_program->program->min_duration; $i++)
                                          <option value="{{ $i }}" @if($i == $assignment->year_of_study ) selected="selected" @else disabled="disabled" @endif>{{ $i }}</option>
                                          @endfor
                                        </select>
                                      </div>
                                    </div>
                                      <div class="row">
                                      <div class="form-group col-4">
                                      {!! Form::label('','Category') !!}
                                      <select name="category" class="form-control" required>
                                         <option value="">Select Category</option>
                                         <option value="COMPULSORY" @if($assignment->category == 'COMPULSORY') selected="selected" @endif>Compulsory</option>
                                         <option value="OPTIONAL" @if($assignment->category == 'OPTIONAL') selected="selected" @endif>Optional</option>
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                      {!! Form::label('','Type') !!}
                                      <select name="type" class="form-control" required>
                                         <option value="">Select Type</option>
                                         <option value="CORE" @if($assignment->type == 'CORE') selected="selected" @endif>Core</option>
                                         <option value="FUNDAMENTAL" @if($assignment->type == 'FUNDAMENTAL') selected="selected" @endif>Fundamental</option>
                                      </select>
                                      </div>
                                      <div class="form-group col-4">
                                      {!! Form::label('','Semester') !!}
                                      <select name="semester_id" class="form-control" required id="ss-semester-{{ $assignment->module->id }}">
                                         <option value="">Select Semester</option>
                                         @foreach($semesters as $semester)
                                         <option value="{{ $semester->id }}" @if($assignment->semester_id == $semester->id) selected="selected" @else disabled="disabled" @endif>{{ $semester->name }}</option>
                                         @endforeach
                                      </select>
                                      </div>
                                          {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                                          {!! Form::input('hidden','campus_program_id',$campus_program->id) !!}
                                          {!! Form::input('hidden','program_module_assignment_id',$assignment->id) !!}

                                      
                                    </div>
                                    <div class="row">
                                <div class="form-group col-4">
                                  {!! Form::label('','Coursework max mark') !!}
                                  {!! Form::input('number','course_work_min_mark',$assignment->course_work_min_mark,$course_work_min_mark) !!}
                                </div>
                                <div class="form-group col-4">
                                  {!! Form::label('','Coursework percentage pass') !!}
                                  {!! Form::input('number','course_work_percentage_pass',$assignment->course_work_percentage_pass,$course_work_percentage_pass) !!}
                                </div>
                                <div class="form-group col-4">
                                 {!! Form::label('','Coursework pass score') !!}
                                 {!! Form::input('number','course_work_pass_score',$assignment->course_work_pass_score,$course_work_pass_score) !!}

                                 {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                              </div>
                             </div>
                             <div class="row">
                              
                              <div class="form-group col-6">
                                  {!! Form::label('','Final max mark') !!}
                                  {!! Form::input('number','final_min_mark',$assignment->final_min_mark,$final_min_mark) !!}
                              </div>
                              <div class="form-group col-6">
                                  {!! Form::label('','Final percentage pass') !!}
                                  {!! Form::input('number','final_percentage_pass',$assignment->final_percentage_pass,$final_percentage_pass) !!}
                              </div>
                            </div>
                            <div class="row">
                               <div class="form-group col-6">
                                  {!! Form::label('','Final pass score') !!}
                                  {!! Form::input('number','final_pass_score',$assignment->final_pass_score,$final_pass_score) !!}
                              </div>
                              <div class="form-group col-6">
                                  {!! Form::label('','Module pass mark') !!}
                                  {!! Form::input('number','module_pass_mark',$assignment->module_pass_mark,$module_pass_mark) !!}
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
                      
                      @can('delete-programme-module-assignment')
                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-assignment-{{ $assignment->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-delete-assignment-{{ $assignment->id }}">
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this module assignment from this programme?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/program-module-assignment/'.$assignment->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Module Assigned') }}</h3>
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
