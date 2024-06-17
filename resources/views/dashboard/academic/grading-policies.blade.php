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
            <h1>{{ __('Grading System') }}</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">{{ __('Grading System') }}</li>
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
                 {!! Form::open(['url'=>'academic/grading-policies','class'=>'ss-form-processing','method'=>'GET']) !!}
                   
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
            @can('add-grading-policy')
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Add Grading System - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
                @php
                    $min_score = [
                       'class'=>'form-control',
                       'placeholder'=>'Min score',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $max_score = [
                       'class'=>'form-control',
                       'placeholder'=>'Max score',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $grade = [
                       'class'=>'form-control',
                       'placeholder'=>'Grade',
                       'required'=>true
                    ];

                    $point = [
                       'class'=>'form-control',
                       'placeholder'=>'Point',
                       'steps'=>'any',
                       'required'=>true
                    ];

                    $remark = [
                       'class'=>'form-control',
                       'placeholder'=>'Remark',
                       'required'=>true
                    ];
                 @endphp
                 {!! Form::open(['url'=>'academic/grading-policy/store','class'=>'ss-form-processing']) !!}
              <div class="card-body">
                 
                 <div class="row">
                  <div class="form-group col-4">
                     {!! Form::label('','Min score') !!}
                     {!! Form::input('number','min_score',null,$min_score) !!}
                  </div>
                  <div class="form-group col-4">
                     {!! Form::label('','Max score') !!}
                     {!! Form::input('number','max_score',null,$max_score) !!}
                  </div>
                  <div class="form-group col-4">
                     {!! Form::label('','Grade') !!}
                     <select name="grade" class="form-control" required>
                        <option value="">Select Grade</option>
                        <option value="A">A</option>
                        <option value="B+">B+</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="F">F</option>
                     </select>
                  </div>
                 </div>
                 <div class="row">
                  <div class="form-group col-4">
                     {!! Form::label('','Point') !!}
                     <select name="point" class="form-control" required>
                        <option value="">Select Point</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                     </select>
                  </div>
                  <div class="form-group col-4">
                     {!! Form::label('','Remark') !!}
                     <select name="remark" class="form-control" required>
                        <option value="">Select Remark</option>
                        <option value="Excellent">Excellent</option>
                        <option value="Very Good">Very Good</option>
                        <option value="Good">Good</option>
                        <option value="Satisfactory">Satisfactory</option>
                        <option value="Poor">Poor</option>
                        <option value="Failure">Failure</option>
                     </select>

                     {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                  </div>
                    <div class="form-group col-4">
                      {!! Form::label('','NTA Level') !!}
                      <select name="nta_level_id" class="form-control" required>
                         <option value="">Select NTA Level</option>
                         @foreach($nta_levels as $level)
                         <option value="{{ $level->id }}">{{ $level->name }}</option>
                         @endforeach
                      </select>
                    </div>
                 </div>
                </div>
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">{{ __('Add Grading Policy') }}</button>
                  @if($study_academic_year)
                    @if(count($study_academic_year->gradingPolicies) == 0 && $study_academic_year->id == session('active_academic_year_id'))
                  <a href="{{ url('academic/grading-policy/'.$study_academic_year->id.'/assign-as-previous') }}" class="btn btn-primary ss-right">Assign as Previous</a>
                    @endif
                  @endif
                </div>
              {!! Form::close() !!}
            </div>
            <!-- /.card -->
            @endcan
            @endif

            @if(count($policies) != 0 && $study_academic_year)
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Grading Policies - {{ $study_academic_year->academicYear->year }}</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                 {!! Form::open(['url'=>'academic/grading-policies','method'=>'GET']) !!}
                <div class="row ss-margin-bottom">
                  {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}
                </div>
                {!! Form::close() !!}
                <table id="example2" class="table table-bordered table-hover ss-margin-top ss-paginated-table">
                  <thead>
                  <tr>
                    <th>SN</th>
                    <th>Min Score</th>
                    <th>Max Score</th>
                    <th>Grade</th>
                    <th>Point</th>
                    <th>Remark</th>
                    <th>NTA Level</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($policies as $key=>$policy)
                  <tr>                    
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $policy->min_score }}</td>
                    <td>{{ $policy->max_score }}</td>
                    <td>{{ $policy->grade }}</td>
                    <td>{{ $policy->point }}</td>
                    <td>{{ $policy->remark }}</td>
                    <td>{{ $policy->ntaLevel->name }}</td>
                    <td>
                      @can('edit-grading-policy')
                      <a class="btn btn-info btn-sm" @if($study_academic_year->id == session('active_academic_year_id')) href="#" data-toggle="modal" data-target="#ss-edit-policy-{{ $policy->id }}" @else disabled="disabled" @endif>
                              <i class="fas fa-pencil-alt">
                              </i>
                              Edit
                       </a>
                      @endcan

                       <div class="modal fade" id="ss-edit-policy-{{ $policy->id }}">
                        <div class="modal-dialog modal-lg">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h4 class="modal-title"><i class="fa fa-exclamation-sign"></i> Edit Grading System</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              @php
                                  $min_score = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Min score',
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $max_score = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Max score',
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $grade = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Grade',
                                     'required'=>true
                                  ];

                                  $point = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Point',
                                     'steps'=>'any',
                                     'required'=>true
                                  ];

                                  $remark = [
                                     'class'=>'form-control',
                                     'placeholder'=>'Remark',
                                     'required'=>true
                                  ];
                               @endphp
                               {!! Form::open(['url'=>'academic/grading-policy/update','class'=>'ss-form-processing']) !!}

                               <div class="row">
                                <div class="form-group col-4">
                                   {!! Form::label('','Min score') !!}
                                   {!! Form::input('number','min_score',$policy->min_score,$min_score) !!}
                                </div>
                                <div class="form-group col-4">
                                   {!! Form::label('','Max score') !!}
                                   {!! Form::input('number','max_score',$policy->max_score,$max_score) !!}
                                </div>
                                <div class="form-group col-4">
                                   {!! Form::label('','Grade') !!}
                                   <select name="grade" class="form-control" disabled="disabled">
                                      <option value="">Select Grade</option>
                                      <option value="A" @if($policy->grade == 'A') selected="selected" @endif>A</option>
                                      <option value="B+" @if($policy->grade == 'B+') selected="selected" @endif>B+</option>
                                      <option value="B" @if($policy->grade == 'B') selected="selected" @endif>B</option>
                                      <option value="C" @if($policy->grade == 'C') selected="selected" @endif>C</option>
                                      <option value="D" @if($policy->grade == 'D') selected="selected" @endif>D</option>
                                      <option value="F" @if($policy->grade == 'F') selected="selected" @endif>F</option>
                                   </select>
                                   {!! Form::input('hidden','grade',$policy->grade) !!}
                                </div>
                               </div>
                               <div class="row">
                                <div class="form-group col-4">
                                   {!! Form::label('','Point') !!}
                                   <select name="point" class="form-control" required>
                                      <option value="">Select Point</option>
                                      <option value="0" @if($policy->point == '0') selected="selected" @endif>0</option>
                                      <option value="1" @if($policy->point == '1') selected="selected" @endif>1</option>
                                      <option value="2" @if($policy->point == '2') selected="selected" @endif>2</option>
                                      <option value="3" @if($policy->point == '3') selected="selected" @endif>3</option>
                                      <option value="4" @if($policy->point == '4') selected="selected" @endif>4</option>
                                      <option value="5" @if($policy->point == '5') selected="selected" @endif>5</option>
                                   </select>
                                </div>
                                <div class="form-group col-4">
                                   {!! Form::label('','Remark') !!}
                                   <select name="remark" class="form-control" required>
                                      <option value="">Select Remark</option>
                                      <option value="Excellent" @if($policy->remark == 'Excellent') selected="selected" @endif>Excellent</option>
                                      <option value="Very Good" @if($policy->remark == 'Very Good') selected="selected" @endif>Very Good</option>
                                      <option value="Good" @if($policy->remark == 'Good') selected="selected" @endif>Good</option>
                                      <option value="Satisfactory" @if($policy->remark == 'Satisfactory') selected="selected" @endif>Satisfactory</option>
                                      <option value="Poor" @if($policy->remark == 'Poor') selected="selected" @endif>Poor</option>
                                      <option value="Failure" @if($policy->remark == 'Failure') selected="selected" @endif>Failure</option>
                                   </select>

                                   {!! Form::input('hidden','study_academic_year_id',$study_academic_year->id) !!}

                                   {!! Form::input('hidden','grading_policy_id',$policy->id) !!}
                                </div>
                                  <div class="form-group col-4">
                                    {!! Form::label('','NTA Level') !!}
                                    <select name="nta_level_id" class="form-control" disabled="disabled">
                                       <option value="">Select NTA Level</option>
                                       @foreach($nta_levels as $level)
                                       <option value="{{ $level->id }}" @if($policy->nta_level_id == $level->id) selected="selected" @endif>{{ $level->name }}</option>
                                       @endforeach
                                    </select>
                                    {!! Form::input('hidden','nta_level_id',$policy->nta_level_id) !!}
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
                      
                      @can('delete-grading-policy')
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
                                       <p id="ss-confirmation-text">Are you sure you want to delete this grading system from the list?</p>
                                       <div class="ss-form-controls">
                                         <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                         <a href="{{ url('academic/grading-policy/'.$policy->id.'/destroy') }}" class="btn btn-danger">Delete</a>
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
                <h3 class="card-title">{{ __('No Grading Policy Created') }}</h3>
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
