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
            <h1>{{ __('Examination Policies') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Examination Policies') }}</li>
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
                 {!! Form::open(['url'=>'academic/examination-policies','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Add Examination Policy - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
                @php
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
                       'required'=>true
                    ];

                    $final_min_mark = [
                       'class'=>'form-control ss-final-min-mark',
                       'placeholder'=>'Final min mark',
                       'id'=>'ss-final-min-mark',
                       'steps'=>'any',
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
                 {!! Form::open(['url'=>'academic/examination-policy/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 

                 <div class="row">
                    <div class="form-group col-4">
                      {!! Form::label('','NTA level') !!}
                      <select name="nta_level_id" class="form-control" required>
                         <option value="">Select NTA Level</option>
                         @foreach($nta_levels as $level)
                         <option value="{{ $level->id }}">{{ $level->name }}</option>
                         @endforeach
                      </select>
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Course work min mark') !!}
                      {!! Form::input('number','course_work_min_mark',null,$course_work_min_mark) !!}
                    </div>
                    <div class="form-group col-4">
                      {!! Form::label('','Course work percentage pass') !!}
                      {!! Form::input('number','course_work_percentage_pass',null,$course_work_percentage_pass) !!}
                    </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-4">
                     {!! Form::label('','Course work pass score') !!}
                     {!! Form::input('number','course_work_pass_score',null,$course_work_pass_score) !!}

                     {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  </div>
                  <div class="form-group col-4">
                      {!! Form::label('','Final min mark') !!}
                      {!! Form::input('number','final_min_mark',null,$final_min_mark) !!}
                  </div>
                  <div class="form-group col-4">
                      {!! Form::label('','Final percentage pass') !!}
                      {!! Form::input('number','final_percentage_pass',null,$final_percentage_pass) !!}
                  </div>
                </div>
                <div class="row">
                   <div class="form-group col-4">
                      {!! Form::label('','Final pass score') !!}
                      {!! Form::input('number','final_pass_score',null,$final_pass_score) !!}
                  </div>
                  <div class="form-group col-4">
                      {!! Form::label('','Module pass mark') !!}
                      {!! Form::input('number','module_pass_mark',null,$module_pass_mark) !!}
                  </div>
                  <div class="form-group col-4">
                      {!! Form::label('','Programme type') !!}
                      <select name="type" class="form-control" required>
                         <option value="">Select Programme Type</option>
                         <option value="COMMUNITY DEVELOPMENT">Community Development</option>
                         <option value="NON-COMMUNITY DEVELOPMENT">Non-Community Development</option>
                      </select>
                  </div>
                </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Examination Policy') }}</button>
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endif

            @if(count($examination_policies) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Examination Policies</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 
                <table id="example2" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                    <th>Study Academic Year</th>
                    <th>NTA Level</th>
                    <th>CW Min Mark</th>
                    <th>CW Perc Pass</th>
                    <th>CW Pass Score</th>
                    <th>Final Min Mark</th>
                    <th>Final Perce Pass</th>
                    <th>Final Pass Score</th>
                    <th>Module Pass Mark</th>
                    <th>Programme Type</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($examination_policies as $policy)
                  <tr>
                    <td>{{ $policy->studyAcademicYear->academicYear->year }}</td>
                    <td>{{ $policy->ntaLevel->name }}</td>
                    <td>{{ $policy->course_work_min_mark }}</td>
                    <td>{{ $policy->course_work_percentage_pass }}</td>
                    <td>{{ $policy->course_work_pass_score }}</td>
                    <td>{{ $policy->final_min_mark }}</td>
                    <td>{{ $policy->final_percentage_pass }}</td>
                    <td>{{ $policy->final_pass_score }}</td>
                    <td>{{ $policy->module_pass_mark }}</td>
                    <td>{{ $policy->type }}</td>
                    <td>
                      <a class="btn btn-info btn-sm" href="#" data-toggle="modal" data-target="#ss-edit-policy-{{ $policy->id }}">
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                       <div class="modal fade" id="ss-edit-policy-{{ $policy->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Examination Policy</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                               @php
                                  $course_work_min_mark = [
                                     'class'=>'form-control ss-course-work-min-mark',
                                     'placeholder'=>'Course work min mark-'. $policy->id,
                                     'data-target'=>'#ss-final-min-mark-'.$policy->id,
                                     'id'=>'ss-course-work-min-mark-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_percentage_pass = [
                                     'class'=>'form-control ss-course-work-percentage-pass',
                                     'placeholder'=>'Course work percentage pass',
                                     'data-from'=>'#ss-course-work-min-mark-'.$policy->id,
                                     'data-target'=>'#ss-course-work-pass-score-'.$policy->id,
                                     'id'=>'ss-course-work-percentage-pass-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $course_work_pass_score = [
                                     'class'=>'form-control ss-course-work-pass-score',
                                     'placeholder'=>'Course work pass score',
                                     'id'=>'ss-course-work-pass-score-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_min_mark = [
                                     'class'=>'form-control ss-final-min-mark',
                                     'placeholder'=>'Final min mark',
                                     'id'=>'ss-final-min-mark-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_percentage_pass = [
                                     'class'=>'form-control ss-final-percentage-pass',
                                     'placeholder'=>'Final percentage pass',
                                     'data-from'=>'#ss-final-min-mark-'.$policy->id,
                                     'data-target'=>'#ss-final-pass-score-'.$policy->id,
                                     'id'=>'ss-final-percentage-pass-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $final_pass_score = [
                                     'class'=>'form-control ss-final-pass-score',
                                     'placeholder'=>'Final pass score',
                                     'id'=>'ss-final-pass-score-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $module_pass_mark = [
                                     'class'=>'form-control ss-module-pass-mark',
                                     'placeholder'=>'Module pass mark',
                                     'id'=>'ss-module-pass-mark-'.$policy->id,
                                     'steps'=>'any',
                                     'required'=>true
                                  ];
                               @endphp

                               {!! Form::open(['url'=>'academic/examination-policy/update','class'=>'ss-form-processing']) !!}

                               <div class="row">
                                <div class="form-group col-4">
                                  {!! Form::label('','NTA level') !!}
                                  <select name="nta_level_id" class="form-control" required>
                                     <option value="">Select NTA Level</option>
                                     @foreach($nta_levels as $level)
                                     <option value="{{ $level->id }}" @if($policy->nta_level_id == $level->id) selected="selected" @endif>{{ $level->name }}</option>
                                     @endforeach
                                  </select>
                                </div>
                                <div class="form-group col-4">
                                  {!! Form::label('','Course work min mark') !!}
                                  {!! Form::input('number','course_work_min_mark',$policy->course_work_min_mark,$course_work_min_mark) !!}
                                </div>
                                <div class="form-group col-4">
                                  {!! Form::label('','Course work percentage pass') !!}
                                  {!! Form::input('number','course_work_percentage_pass',$policy->course_work_percentage_pass,$course_work_percentage_pass) !!}
                                </div>
                             </div>
                             <div class="row">
                              <div class="form-group col-4">
                                 {!! Form::label('','Course work pass score') !!}
                                 {!! Form::input('number','course_work_pass_score',$policy->course_work_pass_score,$course_work_pass_score) !!}

                                 {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                              </div>
                              <div class="form-group col-4">
                                  {!! Form::label('','Final min mark') !!}
                                  {!! Form::input('number','final_min_mark',$policy->final_min_mark,$final_min_mark) !!}
                              </div>
                              <div class="form-group col-4">
                                  {!! Form::label('','Final percentage pass') !!}
                                  {!! Form::input('number','final_percentage_pass',$policy->final_percentage_pass,$final_percentage_pass) !!}
                              </div>
                            </div>
                            <div class="row">
                               <div class="form-group col-4">
                                  {!! Form::label('','Final pass score') !!}
                                  {!! Form::input('number','final_pass_score',$policy->final_pass_score,$final_pass_score) !!}
                              </div>
                              <div class="form-group col-4">
                                  {!! Form::label('','Module pass mark') !!}
                                  {!! Form::input('number','module_pass_mark',$policy->module_pass_mark,$module_pass_mark) !!}
                              </div>
                              <div class="form-group col-4">
                                  {!! Form::label('','Programme type') !!}
                                  <select name="type" class="form-control" required>
                                     <option value="">Select Programme Type</option>
                                     <option value="COMMUNITY DEVELOPMENT" @if($policy->type == 'COMMUNITY DEVELOPMENT') selected="selected" @endif>Community Development</option>
                                     <option value="NON-COMMUNITY DEVELOPMENT" @if($policy->type == 'NON-COMMUNITY DEVELOPMENT') selected="selected" @endif>Non-Community Development</option>
                                  </select>

                                  {!! Form::input('hidden','examination_policy_id',$policy->id) !!}
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

                      <a class="btn btn-danger btn-sm" href="#" data-toggle="modal" data-target="#ss-delete-policy-{{ $policy->id }}">
                              <i class="fas fa-trash">
                              </i>
                              Delete
                       </a>

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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this examination policy from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/examination-policy/'.$policy->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Examination Policy Created') }}</h3>
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
